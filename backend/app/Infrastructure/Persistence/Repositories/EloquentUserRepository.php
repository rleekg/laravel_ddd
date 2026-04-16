<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Identity\Entities\User;
use App\Domain\Identity\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Override;

final class EloquentUserRepository implements UserRepositoryInterface
{
    #[Override]
    public function findByLogin(string $login): ?User
    {
        $model = UserModel::where('login', $login)->first();
        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    #[Override]
    public function existsByLogin(string $login): bool
    {
        return UserModel::where('login', $login)->exists();
    }

    #[Override]
    public function save(User $user): User
    {
        if ($user->id === null) {
            $model = UserModel::create([
                'name' => $user->name,
                'login' => $user->login,
                'password' => $user->passwordHash,
            ]);
        } else {
            $model = UserModel::findOrFail($user->id);
            $model->update([
                'name' => $user->name,
                'login' => $user->login,
                'password' => $user->passwordHash,
            ]);
        }

        return $this->toEntity($model);
    }

    private function toEntity(UserModel $model): User
    {
        return new User($model->id, $model->name, $model->login, $model->password);
    }
}
