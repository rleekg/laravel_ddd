<?php

declare(strict_types=1);

namespace App\Domain\Identity\Entities;

final readonly class User
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $login,
        public string $passwordHash,
    ) {}
}
