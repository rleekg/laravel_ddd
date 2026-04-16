<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_user_successfully_and_exits_with_zero(): void
    {
        $this->artisan('user:create', [
            'name' => 'Ivan Petrov',
            'login' => 'ivan',
            'password' => 'secret123',
        ])
            ->expectsOutput('User "Ivan Petrov" (login: ivan) created successfully.')
            ->assertExitCode(0);
    }

    public function test_user_exists_in_database_after_creation(): void
    {
        $this->artisan('user:create', [
            'name' => 'Ivan Petrov',
            'login' => 'ivan',
            'password' => 'secret123',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name' => 'Ivan Petrov',
            'login' => 'ivan',
        ]);
    }

    public function test_user_balance_record_created_with_zero_amount(): void
    {
        $this->artisan('user:create', [
            'name' => 'Ivan Petrov',
            'login' => 'ivan',
            'password' => 'secret123',
        ])->assertExitCode(0);

        $user = UserModel::where('login', 'ivan')->firstOrFail();

        $this->assertDatabaseHas('user_balances', [
            'user_id' => $user->id,
            'amount' => '0.00',
        ]);
    }

    public function test_duplicate_login_exits_with_one_and_shows_error(): void
    {
        UserModel::create([
            'name' => 'Existing User',
            'login' => 'ivan',
            'password' => Hash::make('password'),
        ]);

        $this->artisan('user:create', [
            'name' => 'Another Ivan',
            'login' => 'ivan',
            'password' => 'otherpass',
        ])
            ->expectsOutput('Login "ivan" already exists.')
            ->assertExitCode(1);
    }

    public function test_duplicate_login_does_not_create_second_user(): void
    {
        UserModel::create([
            'name' => 'Existing User',
            'login' => 'ivan',
            'password' => Hash::make('password'),
        ]);

        $this->artisan('user:create', [
            'name' => 'Another Ivan',
            'login' => 'ivan',
            'password' => 'otherpass',
        ])->assertExitCode(1);

        $this->assertSame(1, UserModel::where('login', 'ivan')->count());
    }
}
