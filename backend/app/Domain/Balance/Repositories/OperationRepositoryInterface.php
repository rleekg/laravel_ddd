<?php

declare(strict_types=1);

namespace App\Domain\Balance\Repositories;

use App\Domain\Balance\Entities\Operation;

interface OperationRepositoryInterface
{
    public function save(Operation $operation): Operation;

    public function update(Operation $operation): void;

    public function findById(int $id): ?Operation;

    public function markAsFailed(int $id): void;

    public function findRecentByUserId(int $userId, int $limit = 5): array;

    public function findPaginatedByUserId(
        int $userId,
        string $sort,
        ?string $search,
        int $page,
        int $perPage = 15,
    ): array;
}
