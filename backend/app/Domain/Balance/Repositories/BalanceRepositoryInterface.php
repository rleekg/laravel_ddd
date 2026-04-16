<?php

declare(strict_types=1);

namespace App\Domain\Balance\Repositories;

use App\Domain\Balance\Aggregates\UserBalance;

interface BalanceRepositoryInterface
{
    public function findByUserId(int $userId): ?UserBalance;

    /** Finds balance with a pessimistic write lock (must be called inside a transaction). */
    public function findByUserIdForUpdate(int $userId): ?UserBalance;

    public function save(UserBalance $balance): void;

    public function createForUser(int $userId): UserBalance;
}
