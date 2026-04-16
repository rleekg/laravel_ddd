<?php

declare(strict_types=1);

namespace App\Infrastructure\Queue\Jobs;

use App\Domain\Balance\Aggregates\UserBalance;
use App\Domain\Balance\Entities\Operation;
use App\Domain\Balance\Exceptions\InsufficientFundsException;
use App\Domain\Balance\Repositories\BalanceRepositoryInterface;
use App\Domain\Balance\Repositories\OperationRepositoryInterface;
use App\Domain\Balance\Services\BalanceDomainService;
use App\Domain\Balance\ValueObjects\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class ProcessBalanceOperationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(private readonly int $operationId) {}

    public function handle(
        BalanceRepositoryInterface $balanceRepo,
        OperationRepositoryInterface $operationRepo,
        BalanceDomainService $domainService,
    ): void {
        $operation = $operationRepo->findById($this->operationId);

        if (! $operation instanceof Operation || $operation->status !== 'pending') {
            // Already processed or missing — idempotent exit
            return;
        }

        try {
            DB::transaction(function () use ($operation, $balanceRepo, $operationRepo): void {
                // Pessimistic lock inside transaction to prevent race conditions
                $balance = $balanceRepo->findByUserIdForUpdate($operation->userId);

                if (! $balance instanceof UserBalance) {
                    throw new RuntimeException("Balance for user {$operation->userId} not found.");
                }

                // Apply credit/debit on existing Operation (no new Operation is created)
                $money = new Money($operation->amount);

                if ($operation->type === 'credit') {
                    $balance->credit($money);
                } else {
                    $balance->debit($money); // throws InsufficientFundsException if needed
                }

                $balanceRepo->save($balance);
                $operationRepo->update($operation->asCompleted());
            });
        } catch (InsufficientFundsException) {
            $operationRepo->markAsFailed($this->operationId);
            // Do not retry for business rule violations
            $this->fail(new RuntimeException("Insufficient funds for operation {$this->operationId}"));
        }
    }

    public function failed(Throwable $exception): void
    {
        // Mark as failed on final retry (non-InsufficientFunds errors)
        app(OperationRepositoryInterface::class)->markAsFailed($this->operationId);
    }
}
