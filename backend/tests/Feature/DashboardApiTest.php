<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\OperationModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserBalanceModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_authenticated_user_gets_200_with_balance_and_recent_operations(): void
    {
        $user = UserModel::create([
            'name' => 'Test User',
            'login' => 'testuser',
            'password' => Hash::make('password'),
        ]);

        UserBalanceModel::create([
            'user_id' => $user->id,
            'amount' => '1500.00',
        ]);

        OperationModel::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => '1000.00',
            'description' => 'Top-up',
            'status' => 'completed',
        ]);

        OperationModel::create([
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => '500.00',
            'description' => 'Payment',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user, 'web')->getJson('/api/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'balance',
            'recent_operations' => [
                '*' => ['id', 'type', 'amount', 'description', 'status', 'created_at'],
            ],
        ]);
        $response->assertJsonPath('balance', '1500.00');
        $this->assertCount(2, $response->json('recent_operations'));
    }

    public function test_user_with_no_operations_gets_empty_recent_operations(): void
    {
        $user = UserModel::create([
            'name' => 'Empty User',
            'login' => 'emptyuser',
            'password' => Hash::make('password'),
        ]);

        UserBalanceModel::create([
            'user_id' => $user->id,
            'amount' => '0.00',
        ]);

        $response = $this->actingAs($user, 'web')->getJson('/api/dashboard');

        $response->assertStatus(200);
        $response->assertJsonPath('recent_operations', []);
    }

    public function test_dashboard_returns_maximum_five_recent_operations_when_seven_exist(): void
    {
        $user = UserModel::create([
            'name' => 'Busy User',
            'login' => 'busyuser',
            'password' => Hash::make('password'),
        ]);

        UserBalanceModel::create([
            'user_id' => $user->id,
            'amount' => '700.00',
        ]);

        for ($i = 1; $i <= 7; $i++) {
            OperationModel::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => '100.00',
                'description' => "Operation {$i}",
                'status' => 'completed',
            ]);
        }

        $response = $this->actingAs($user, 'web')->getJson('/api/dashboard');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('recent_operations'));
    }

    public function test_dashboard_does_not_return_other_users_operations(): void
    {
        $userA = UserModel::create([
            'name' => 'User A',
            'login' => 'usera',
            'password' => Hash::make('password'),
        ]);

        $userB = UserModel::create([
            'name' => 'User B',
            'login' => 'userb',
            'password' => Hash::make('password'),
        ]);

        UserBalanceModel::create(['user_id' => $userA->id, 'amount' => '0.00']);
        UserBalanceModel::create(['user_id' => $userB->id, 'amount' => '200.00']);

        OperationModel::create([
            'user_id' => $userB->id,
            'type' => 'credit',
            'amount' => '200.00',
            'description' => 'B top-up',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($userA, 'web')->getJson('/api/dashboard');

        $response->assertStatus(200);
        $response->assertJsonPath('recent_operations', []);
    }
}
