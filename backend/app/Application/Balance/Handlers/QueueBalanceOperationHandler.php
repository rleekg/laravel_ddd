<?php

declare(strict_types=1);

namespace App\Application\Balance\Handlers;

use App\Application\Balance\Commands\QueueBalanceOperationCommand;
use App\Domain\Balance\Aggregates\UserBalance;
use App\Domain\Balance\Entities\Operation;
use App\Domain\Balance\Exceptions\InsufficientFundsException;
use App\Domain\Balance\Repositories\BalanceRepositoryInterface;
use App\Domain\Balance\Repositories\OperationRepositoryInterface;
use App\Domain\Balance\ValueObjects\Money;
use App\Domain\Identity\Entities\User;
use App\Domain\Identity\Exceptions\UserNotFoundException;
use App\Domain\Identity\Repositories\UserRepositoryInterface;
use App\Infrastructure\Queue\Jobs\ProcessBalanceOperationJob;

final readonly class QueueBalanceOperationHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private BalanceRepositoryInterface $balanceRepo,
        private OperationRepositoryInterface $operationRepo,
    ) {}

    /**
     * Creates a pending Operation in the DB, then dispatches a Job to process it.
     * Returns the pending Operation with its ID.
     */
    public function handle(QueueBalanceOperationCommand $cmd): Operation
    {
        $user = $this->userRepo->findByLogin($cmd->login);
        if (! $user instanceof User) {
            throw new UserNotFoundException("User \"{$cmd->login}\" not found.");
        }

        $money = new Money($cmd->amount);

        $userId = (int) $user->id;

        if ($cmd->type === 'debit') {
            $balance = $this->balanceRepo->findByUserId($userId);
            if (! $balance instanceof UserBalance || ! $balance->canDebit($money)) {
                $current = $balance?->getAmount() ?? 0.0;
                throw new InsufficientFundsException($current, $cmd->amount);
            }
        }

        $operation = $this->operationRepo->save(
            Operation::pending($userId, $cmd->type, $money, $cmd->description)
        );

        ProcessBalanceOperationJob::dispatch($operation->id)->onQueue('balance');

        return $operation;
    }
}
