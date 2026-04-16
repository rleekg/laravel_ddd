<?php

declare(strict_types=1);

namespace App\Domain\Balance\Aggregates;

use App\Domain\Balance\Exceptions\InsufficientFundsException;
use App\Domain\Balance\ValueObjects\Money;

final class UserBalance
{
    private float $amount;

    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        float $amount,
    ) {
        $this->amount = round($amount, 2);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function canDebit(Money $money): bool
    {
        return $this->amount >= $money->amount;
    }

    public function credit(Money $money): void
    {
        $this->amount = round($this->amount + $money->amount, 2);
    }

    /** @throws InsufficientFundsException */
    public function debit(Money $money): void
    {
        if (! $this->canDebit($money)) {
            throw new InsufficientFundsException($this->amount, $money->amount);
        }
        $this->amount = round($this->amount - $money->amount, 2);
    }
}
