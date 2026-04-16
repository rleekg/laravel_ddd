<?php

declare(strict_types=1);

namespace App\Application\Balance\Commands;

final readonly class ProcessBalanceCommand
{
    public function __construct(
        public string $login,
        public string $type,
        public float $amount,
        public string $description,
    ) {}
}
