<?php

declare(strict_types=1);

namespace App\Domain\Balance\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    public float $amount;

    public function __construct(float $amount)
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Money amount must be > 0, got {$amount}.");
        }
        $this->amount = round($amount, 2);
    }

    public function add(self $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function subtract(self $other): self
    {
        return new self($this->amount - $other->amount);
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->amount > $other->amount;
    }
}
