<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Balance\Entities\Operation;
use App\Domain\Balance\Repositories\OperationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\OperationModel;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Collection;
use Override;

final class EloquentOperationRepository implements OperationRepositoryInterface
{
    #[Override]
    public function save(Operation $operation): Operation
    {
        $model = OperationModel::create([
            'user_id' => $operation->userId,
            'type' => $operation->type,
            'amount' => $operation->amount,
            'description' => $operation->description,
            'status' => $operation->status,
        ]);

        return $this->toEntity($model);
    }

    #[Override]
    public function update(Operation $operation): void
    {
        OperationModel::where('id', $operation->id)->update(['status' => $operation->status]);
    }

    #[Override]
    public function findById(int $id): ?Operation
    {
        $model = OperationModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    #[Override]
    public function markAsFailed(int $id): void
    {
        OperationModel::where('id', $id)->where('status', 'pending')->update(['status' => 'failed']);
    }

    #[Override]
    public function findRecentByUserId(int $userId, int $limit = 5): array
    {
        /** @var Collection<int, OperationModel> $results */
        $results = OperationModel::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $results->map(fn (OperationModel $m): array => $this->toArray($m))->all();
    }

    #[Override]
    public function findPaginatedByUserId(
        int $userId,
        string $sort,
        ?string $search,
        int $page,
        int $perPage = 15,
    ): array {
        $query = OperationModel::where('user_id', $userId);

        if ($search !== null && $search !== '') {
            $query->where('description', 'LIKE', "%{$search}%");
        }

        $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        /** @var array<int, OperationModel> $items */
        $items = $paginator->items();

        return [
            'data' => collect($items)->map(fn (OperationModel $m): array => $this->toArray($m))->all(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    private function toEntity(OperationModel $model): Operation
    {
        return new Operation(
            $model->id,
            $model->user_id,
            $model->type,
            (float) $model->amount,
            $model->description,
            $model->status,
            $model->created_at ? DateTimeImmutable::createFromMutable($model->created_at->toDateTime()) : null,
        );
    }

    private function toArray(OperationModel $model): array
    {
        return [
            'id' => $model->id,
            'type' => $model->type,
            'amount' => number_format((float) $model->amount, 2, '.', ''),
            'description' => $model->description,
            'status' => $model->status,
            'created_at' => $model->created_at?->toISOString(),
        ];
    }
}
