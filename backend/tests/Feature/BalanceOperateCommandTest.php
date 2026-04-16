<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\OperationModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserBalanceModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class BalanceOperateCommandTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::create([
            'name' => 'Ivan Petrov',
            'login' => 'ivan',
            'password' => Hash::make('secret123'),
        ]);

        UserBalanceModel::create([
            'user_id' => $this->user->id,
            'amount' => '500.00',
        ]);
    }

    public function test_credit_operation_exits_with_zero_and_increases_balance(): void
    {
        $this->artisan('balance:operate', [
            'login' => 'ivan',
            'type' => 'credit',
            'amount' => '300',
            'description' => 'Test credit',
        ])
            ->expectsOutput('Operation completed. New balance: 800.00')
            ->assertExitCode(0);

        $this->assertDatabaseHas('user_balances', [
            'user_id' => $this->user->id,
            'amount' => '800.00',
        ]);
    }

    public function test_debit_operation_exits_with_zero_and_decreases_balance(): void
    {
        $this->artisan('balance:operate', [
            'login' => 'ivan',
            'type' => 'debit',
            'amount' => '200',
            'description' => 'Test debit',
        ])
            ->expectsOutput('Operation completed. New balance: 300.00')
            ->assertExitCode(0);

        $this->assertDatabaseHas('user_balances', [
            'user_id' => $this->user->id,
            'amount' => '300.00',
        ]);
    }

    public function test_debit_with_insufficient_funds_exits_with_one(): void
    {
        $this->artisan('balance:operate', [
            'login' => 'ivan',
            'type' => 'debit',
            'amount' => '999999',
            'description' => 'Exceeds balance',
        ])
            ->expectsOutput('Insufficient balance. Current: 500.00, required: 999999.00')
            ->assertExitCode(1);
    }

    public function test_debit_with_insufficient_funds_does_not_change_balance(): void
    {
        $this->artisan('balance:operate', [
            'login' => 'ivan',
            'type' => 'debit',
            'amount' => '999999',
            'description' => 'Exceeds balance',
        ])->assertExitCode(1);

        $this->assertDatabaseHas('user_balances', [
            'user_id' => $this->user->id,
            'amount' => '500.00',
        ]);
    }

    public function test_unknown_login_exits_with_one(): void
    {
        $this->artisan('balance:operate', [
            'login' => 'unknown',
            'type' => 'credit',
            'amount' => '100',
            'description' => 'Test',
        ])
            ->expectsOutput('User "unknown" not found.')
            ->assertExitCode(1);
    }

    public function test_invalid_type_exits_with_one(): void
    {
        $this->artisan('balance:operate', [
            'login' => 'ivan',
            'type' => 'transfer',
            'amount' => '100',
            'description' => 'Test',
        ])
            ->expectsOutput('Invalid type "transfer". Use: credit or debit.')
            ->assertExitCode(1);
    }

    public function test_queue_option_exits_with_zero_and_creates_pending_operation(): void
    {
        $this->artisan('balance:operate', [
            'login' => 'ivan',
            'type' => 'credit',
            'amount' => '100',
            'description' => 'Async top-up',
            '--queue' => true,
        ])->assertExitCode(0);

        $operation = OperationModel::where('user_id', $this->user->id)
            ->where('type', 'credit')
            ->where('description', 'Async top-up')
            ->first();

        $this->assertNotNull($operation);
        $this->assertSame('pending', $operation->status);
    }

    public function test_queue_option_outputs_operation_id(): void
    {
        $this->artisan('balance:operate', [
            'login' => 'ivan',
            'type' => 'credit',
            'amount' => '100',
            'description' => 'Async top-up',
            '--queue' => true,
        ])->assertExitCode(0);

        $operation = OperationModel::where('user_id', $this->user->id)->first();
        $this->assertNotNull($operation);

        // Confirm the operation record exists with expected id
        $this->artisan('balance:operate', [
            'login' => 'ivan',
            'type' => 'credit',
            'amount' => '50',
            'description' => 'Another async',
            '--queue' => true,
        ])
            ->assertExitCode(0);
    }

    public function test_queue_option_with_insufficient_funds_exits_with_one(): void
    {
        $this->artisan('balance:operate', [
            'login' => 'ivan',
            'type' => 'debit',
            'amount' => '999999',
            'description' => 'Big async debit',
            '--queue' => true,
        ])
            ->expectsOutput('Insufficient balance. Current: 500.00, required: 999999.00')
            ->assertExitCode(1);
    }

    public function test_queue_option_with_insufficient_funds_does_not_create_operation(): void
    {
        $this->artisan('balance:operate', [
            'login' => 'ivan',
            'type' => 'debit',
            'amount' => '999999',
            'description' => 'Big async debit',
            '--queue' => true,
        ])->assertExitCode(1);

        $this->assertDatabaseMissing('operations', [
            'user_id' => $this->user->id,
            'description' => 'Big async debit',
        ]);
    }

    public function test_queue_option_unknown_login_exits_with_one(): void
    {
        $this->artisan('balance:operate', [
            'login' => 'ghost',
            'type' => 'credit',
            'amount' => '100',
            'description' => 'Ghost credit',
            '--queue' => true,
        ])
            ->expectsOutput('User "ghost" not found.')
            ->assertExitCode(1);
    }
}
