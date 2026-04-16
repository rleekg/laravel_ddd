<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Balance\Aggregates\UserBalance;
use App\Domain\Balance\Repositories\BalanceRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\UserBalanceModel;
use Override;
use RuntimeException;

final class EloquentBalanceRepository implements BalanceRepositoryInterface
{
    #[Override]
    public function findByUserId(int $userId): ?UserBalance
    {
        $model = UserBalanceModel::where('user_id', $userId)->first();
        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    #[Override]
    public function findByUserIdForUpdate(int $userId): ?UserBalance
    {
        /** @var UserBalanceModel|null $model */
        $model = UserBalanceModel::where('user_id', $userId)->lockForUpdate()->first();
        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    #[Override]
    public function save(UserBalance $balance): void
    {
        $updated = UserBalanceModel::where('user_id', $balance->userId)
            ->update(['amount' => $balance->getAmount()]);

        if ($updated === 0) {
            throw new RuntimeException("Balance for user {$balance->userId} not found during save.");
        }
    }

    #[Override]
    public function createForUser(int $userId): UserBalance
    {
        $model = UserBalanceModel::create([
            'user_id' => $userId,
            'amount' => '0.00',
        ]);

        return $this->toEntity($model);
    }

    private function toEntity(UserBalanceModel $model): UserBalance
    {
        return new UserBalance($model->id, $model->user_id, (float) $model->amount);
    }
}
