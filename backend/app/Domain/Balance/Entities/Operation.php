<?php

declare(strict_types=1);

namespace App\Domain\Balance\Entities;

use App\Domain\Balance\ValueObjects\Money;
use DateTimeImmutable;

final readonly class Operation
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $type,
        public float $amount,
        public string $description,
        public string $status,
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public static function pending(int $userId, string $type, Money $money, string $description): self
    {
        return new self(null, $userId, $type, $money->amount, $description, 'pending');
    }

    public function asCompleted(): self
    {
        return new self(
            $this->id, $this->userId, $this->type,
            $this->amount, $this->description, 'completed', $this->createdAt
        );
    }

    public function asFailed(): self
    {
        return new self(
            $this->id, $this->userId, $this->type,
            $this->amount, $this->description, 'failed', $this->createdAt
        );
    }
}
