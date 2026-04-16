<?php

declare(strict_types=1);

namespace App\Domain\Identity\Repositories;

use App\Domain\Identity\Entities\User;

interface UserRepositoryInterface
{
    public function findByLogin(string $login): ?User;

    public function existsByLogin(string $login): bool;

    public function save(User $user): User;
}
