<?php

declare(strict_types=1);

namespace App\Application\Balance\Queries;

use App\Domain\Balance\Aggregates\UserBalance;
use App\Domain\Balance\Repositories\BalanceRepositoryInterface;
use App\Domain\Balance\Repositories\OperationRepositoryInterface;

final readonly class DashboardQueryHandler
{
    public function __construct(
        private BalanceRepositoryInterface $balanceRepo,
        private OperationRepositoryInterface $operationRepo,
    ) {}

    public function handle(int $userId): array
    {
        $balance = $this->balanceRepo->findByUserId($userId);
        $operations = $this->operationRepo->findRecentByUserId($userId, 5);

        return [
            'balance' => $balance instanceof UserBalance ? number_format($balance->getAmount(), 2, '.', '') : '0.00',
            'recent_operations' => $operations,
        ];
    }
}
