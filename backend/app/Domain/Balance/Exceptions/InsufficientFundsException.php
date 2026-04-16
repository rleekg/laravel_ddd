<?php

declare(strict_types=1);

namespace App\Domain\Balance\Exceptions;

use DomainException;

final class InsufficientFundsException extends DomainException
{
    public readonly float $current;

    public readonly float $required;

    public function __construct(float $current, float $required)
    {
        parent::__construct(sprintf(
            'Insufficient balance. Current: %.2f, required: %.2f',
            $current,
            $required,
        ));
        $this->current = $current;
        $this->required = $required;
    }
}
