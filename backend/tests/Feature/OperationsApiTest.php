<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\OperationModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserBalanceModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class OperationsApiTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::create([
            'name' => 'Test User',
            'login' => 'testuser',
            'password' => Hash::make('password'),
        ]);

        UserBalanceModel::create([
            'user_id' => $this->user->id,
            'amount' => '0.00',
        ]);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/operations');

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_authenticated_user_gets_200_with_paginated_structure(): void
    {
        OperationModel::create([
            'user_id' => $this->user->id,
            'type' => 'credit',
            'amount' => '100.00',
            'description' => 'First',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user, 'web')->getJson('/api/operations');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'type', 'amount', 'description', 'status', 'created_at'],
            ],
            'current_page',
            'last_page',
            'total',
        ]);
    }

    public function test_sort_asc_returns_oldest_operation_first(): void
    {
        $older = OperationModel::create([
            'user_id' => $this->user->id,
            'type' => 'credit',
            'amount' => '50.00',
            'description' => 'Older operation',
            'status' => 'completed',
            'created_at' => Carbon::now()->subMinutes(10),
            'updated_at' => Carbon::now()->subMinutes(10),
        ]);

        $newer = OperationModel::create([
            'user_id' => $this->user->id,
            'type' => 'credit',
            'amount' => '150.00',
            'description' => 'Newer operation',
            'status' => 'completed',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user, 'web')->getJson('/api/operations?sort=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertSame($older->id, $data[0]['id']);
        $this->assertSame($newer->id, $data[1]['id']);
    }

    public function test_sort_desc_returns_newest_operation_first(): void
    {
        $older = OperationModel::create([
            'user_id' => $this->user->id,
            'type' => 'credit',
            'amount' => '50.00',
            'description' => 'Older operation',
            'status' => 'completed',
            'created_at' => Carbon::now()->subMinutes(10),
            'updated_at' => Carbon::now()->subMinutes(10),
        ]);

        $newer = OperationModel::create([
            'user_id' => $this->user->id,
            'type' => 'credit',
            'amount' => '150.00',
            'description' => 'Newer operation',
            'status' => 'completed',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user, 'web')->getJson('/api/operations?sort=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertSame($newer->id, $data[0]['id']);
        $this->assertSame($older->id, $data[1]['id']);
    }

    public function test_search_returns_only_matching_operations(): void
    {
        OperationModel::create([
            'user_id' => $this->user->id,
            'type' => 'credit',
            'amount' => '100.00',
            'description' => 'Пополнение счёта',
            'status' => 'completed',
        ]);

        OperationModel::create([
            'user_id' => $this->user->id,
            'type' => 'debit',
            'amount' => '50.00',
            'description' => 'Оплата услуг',
            'status' => 'completed',
        ]);

        OperationModel::create([
            'user_id' => $this->user->id,
            'type' => 'credit',
            'amount' => '200.00',
            'description' => 'Пополнение для теста',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user, 'web')
            ->getJson('/api/operations?search='.urlencode('Пополнение'));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);

        foreach ($data as $item) {
            $this->assertStringContainsStringIgnoringCase('Пополнение', $item['description']);
        }
    }

    public function test_invalid_sort_value_returns_422(): void
    {
        $response = $this->actingAs($this->user, 'web')
            ->getJson('/api/operations?sort=invalid');

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors' => ['sort'],
        ]);
    }

    public function test_pagination_returns_correct_page(): void
    {
        // Create 20 operations
        for ($i = 1; $i <= 20; $i++) {
            OperationModel::create([
                'user_id' => $this->user->id,
                'type' => 'credit',
                'amount' => '10.00',
                'description' => "Operation {$i}",
                'status' => 'completed',
                'created_at' => Carbon::now()->addSeconds($i),
                'updated_at' => Carbon::now()->addSeconds($i),
            ]);
        }

        $response = $this->actingAs($this->user, 'web')
            ->getJson('/api/operations?page=2');

        $response->assertStatus(200);
        $response->assertJsonPath('current_page', 2);
        $this->assertGreaterThan(1, $response->json('last_page'));
        $this->assertSame(20, $response->json('total'));
    }

    public function test_pagination_second_page_contains_different_items_than_first(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            OperationModel::create([
                'user_id' => $this->user->id,
                'type' => 'credit',
                'amount' => '10.00',
                'description' => "Operation {$i}",
                'status' => 'completed',
                'created_at' => Carbon::now()->addSeconds($i),
                'updated_at' => Carbon::now()->addSeconds($i),
            ]);
        }

        $page1 = $this->actingAs($this->user, 'web')
            ->getJson('/api/operations?page=1')
            ->json('data');

        $page2 = $this->actingAs($this->user, 'web')
            ->getJson('/api/operations?page=2')
            ->json('data');

        $page1Ids = array_column($page1, 'id');
        $page2Ids = array_column($page2, 'id');

        $this->assertEmpty(array_intersect($page1Ids, $page2Ids));
    }

    public function test_does_not_return_other_users_operations(): void
    {
        $otherUser = UserModel::create([
            'name' => 'Other User',
            'login' => 'otheruser',
            'password' => Hash::make('password'),
        ]);

        UserBalanceModel::create(['user_id' => $otherUser->id, 'amount' => '0.00']);

        OperationModel::create([
            'user_id' => $otherUser->id,
            'type' => 'credit',
            'amount' => '500.00',
            'description' => 'Other user operation',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user, 'web')->getJson('/api/operations');

        $response->assertStatus(200);
        $response->assertJsonPath('total', 0);
        $response->assertJsonPath('data', []);
    }
}
