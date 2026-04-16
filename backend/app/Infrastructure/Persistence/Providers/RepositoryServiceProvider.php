<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Providers;

use App\Domain\Balance\Repositories\BalanceRepositoryInterface;
use App\Domain\Balance\Repositories\OperationRepositoryInterface;
use App\Domain\Identity\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentBalanceRepository;
use App\Infrastructure\Persistence\Repositories\EloquentOperationRepository;
use App\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;
use Override;

final class RepositoryServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(BalanceRepositoryInterface::class, EloquentBalanceRepository::class);
        $this->app->bind(OperationRepositoryInterface::class, EloquentOperationRepository::class);
    }
}
