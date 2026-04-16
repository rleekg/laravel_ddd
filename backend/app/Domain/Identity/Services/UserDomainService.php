<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Identity\Exceptions\UserAlreadyExistsException;
use App\Domain\Identity\Repositories\UserRepositoryInterface;

final class UserDomainService
{
    public function ensureLoginUnique(string $login, UserRepositoryInterface $repo): void
    {
        if ($repo->existsByLogin($login)) {
            throw new UserAlreadyExistsException("Login \"{$login}\" already exists.");
        }
    }
}
