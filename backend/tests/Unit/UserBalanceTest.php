<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Balance\Aggregates\UserBalance;
use App\Domain\Balance\Exceptions\InsufficientFundsException;
use App\Domain\Balance\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UserBalanceTest extends TestCase
{
    public function test_credit_increases_balance(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 500.00);

        $balance->credit(new Money(300.00));

        $this->assertSame(800.00, $balance->getAmount());
    }

    public function test_credit_accumulates_correctly_with_decimal_amounts(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 100.00);

        $balance->credit(new Money(0.01));

        $this->assertSame(100.01, $balance->getAmount());
    }

    public function test_debit_decreases_balance(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 500.00);

        $balance->debit(new Money(200.00));

        $this->assertSame(300.00, $balance->getAmount());
    }

    public function test_debit_reduces_balance_to_zero_when_exact_amount(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 250.00);

        $balance->debit(new Money(250.00));

        $this->assertSame(0.00, $balance->getAmount());
    }

    public function test_debit_throws_insufficient_funds_exception_when_amount_exceeds_balance(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 100.00);

        $this->expectException(InsufficientFundsException::class);

        $balance->debit(new Money(500.00));
    }

    public function test_debit_throws_exception_with_correct_current_and_required_amounts(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 100.00);

        try {
            $balance->debit(new Money(500.00));
            $this->fail('Expected InsufficientFundsException was not thrown.');
        } catch (InsufficientFundsException $e) {
            $this->assertSame(100.00, $e->current);
            $this->assertSame(500.00, $e->required);
        }
    }

    public function test_debit_does_not_modify_balance_when_exception_is_thrown(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 100.00);

        try {
            $balance->debit(new Money(500.00));
        } catch (InsufficientFundsException) {
            // expected
        }

        $this->assertSame(100.00, $balance->getAmount());
    }

    public function test_can_debit_returns_true_when_balance_is_sufficient(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 500.00);

        $this->assertTrue($balance->canDebit(new Money(500.00)));
    }

    public function test_can_debit_returns_true_when_amount_is_less_than_balance(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 500.00);

        $this->assertTrue($balance->canDebit(new Money(100.00)));
    }

    public function test_can_debit_returns_false_when_balance_is_insufficient(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 100.00);

        $this->assertFalse($balance->canDebit(new Money(500.00)));
    }

    public function test_can_debit_returns_false_when_balance_is_zero(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 0.00);

        // Money constructor rejects <= 0, so we test with smallest valid amount
        $this->assertFalse($balance->canDebit(new Money(0.01)));
    }

    public function test_money_throws_invalid_argument_exception_for_zero_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Money(0);
    }

    public function test_money_throws_invalid_argument_exception_for_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Money(-1.00);
    }

    public function test_money_throws_invalid_argument_exception_for_very_small_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Money(-0.01);
    }

    public function test_money_accepts_positive_amount(): void
    {
        $money = new Money(100.00);

        $this->assertSame(100.00, $money->amount);
    }

    public function test_money_rounds_to_two_decimal_places(): void
    {
        $money = new Money(10.999);

        $this->assertSame(11.00, $money->amount);
    }

    public function test_user_balance_constructor_rounds_amount_to_two_decimal_places(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 100.555);

        $this->assertSame(100.56, $balance->getAmount());
    }

    public function test_credit_result_is_rounded_to_two_decimal_places(): void
    {
        $balance = new UserBalance(id: 1, userId: 10, amount: 0.10);

        $balance->credit(new Money(0.20));

        $this->assertSame(0.30, $balance->getAmount());
    }
}
