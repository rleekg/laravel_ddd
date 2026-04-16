<?php

declare(strict_types=1);

namespace App\Application\Balance\Handlers;

use App\Application\Balance\Commands\ProcessBalanceCommand;
use App\Domain\Balance\Entities\Operation;
use App\Domain\Balance\Repositories\BalanceRepositoryInterface;
use App\Domain\Balance\Repositories\OperationRepositoryInterface;
use App\Domain\Balance\Services\BalanceDomainService;
use App\Domain\Balance\ValueObjects\Money;
use App\Domain\Identity\Entities\User;
use App\Domain\Identity\Exceptions\UserNotFoundException;
use App\Domain\Identity\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class ProcessBalanceHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private BalanceRepositoryInterface $balanceRepo,
        private OperationRepositoryInterface $operationRepo,
        private BalanceDomainService $domainService,
    ) {}

    public function handle(ProcessBalanceCommand $cmd): Operation
    {
        $user = $this->userRepo->findByLogin($cmd->login);
        if (! $user instanceof User) {
            throw new UserNotFoundException("User \"{$cmd->login}\" not found.");
        }

        $userId = (int) $user->id;
        $pendingOp = Operation::pending($userId, $cmd->type, new Money($cmd->amount), $cmd->description);

        /** @var Operation $result */
        $result = DB::transaction(function () use ($userId, $pendingOp): Operation {
            // Pessimistic lock prevents race conditions on concurrent debit/credit
            $balance = $this->balanceRepo->findByUserIdForUpdate($userId)
                ?? $this->balanceRepo->createForUser($userId);

            return $this->domainService->process(
                $balance,
                $pendingOp,
                $this->balanceRepo,
                $this->operationRepo,
            );
        });

        return $result;
    }
}
