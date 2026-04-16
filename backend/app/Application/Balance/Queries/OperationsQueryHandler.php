<?php

declare(strict_types=1);

namespace App\Application\Balance\Queries;

use App\Domain\Balance\Repositories\OperationRepositoryInterface;

final readonly class OperationsQueryHandler
{
    public function __construct(
        private OperationRepositoryInterface $operationRepo,
    ) {}

    public function handle(int $userId, string $sort = 'desc', ?string $search = null, int $page = 1): array
    {
        return $this->operationRepo->findPaginatedByUserId($userId, $sort, $search, $page);
    }
}
