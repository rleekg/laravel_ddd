<?php

declare(strict_types=1);

namespace App\Domain\Balance\Services;

use App\Domain\Balance\Aggregates\UserBalance;
use App\Domain\Balance\Entities\Operation;
use App\Domain\Balance\Repositories\BalanceRepositoryInterface;
use App\Domain\Balance\Repositories\OperationRepositoryInterface;
use App\Domain\Balance\ValueObjects\Money;

final class BalanceDomainService
{
    public function process(
        UserBalance $balance,
        Operation $pendingOperation,
        BalanceRepositoryInterface $balanceRepo,
        OperationRepositoryInterface $operationRepo,
    ): Operation {
        $operation = $operationRepo->save($pendingOperation);

        $money = new Money($operation->amount);

        if ($operation->type === 'credit') {
            $balance->credit($money);
        } else {
            $balance->debit($money);
        }

        $balanceRepo->save($balance);

        $completed = $operation->asCompleted();
        $operationRepo->update($completed);

        return $completed;
    }
}
