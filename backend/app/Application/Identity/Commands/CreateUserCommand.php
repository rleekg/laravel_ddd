<?php

declare(strict_types=1);

namespace App\Application\Identity\Commands;

final readonly class CreateUserCommand
{
    public function __construct(
        public string $name,
        public string $login,
        public string $password,
    ) {}
}
