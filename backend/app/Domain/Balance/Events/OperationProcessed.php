<?php

declare(strict_types=1);

namespace App\Domain\Balance\Events;

final readonly class OperationProcessed
{
    public function __construct(
        public int $operationId,
        public int $userId,
        public string $type,
        public float $amount,
        public float $newBalance,
    ) {}
}
