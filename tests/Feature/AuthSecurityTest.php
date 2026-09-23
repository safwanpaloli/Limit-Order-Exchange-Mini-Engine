<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/profile');
        $response->assertStatus(401);
    }

    public function test_login_generates_sanctum_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token']);
    }

    public function test_authenticated_user_can_access_protected_routes()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/profile');
        
        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'assets', 'orders']);
    }

    public function test_user_cannot_cancel_another_users_order()
    {
        $owner = User::factory()->create(['balance' => 1000]);
        $attacker = User::factory()->create(['balance' => 1000]);

        $order = Order::create([
            'user_id' => $owner->id,
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => 10000,
            'amount' => 0.1,
            'status' => 1
        ]);

        Sanctum::actingAs($attacker);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");
        
        // Should be forbidden (403)
        $response->assertStatus(403);
    }

    public function test_user_can_cancel_their_own_order()
    {
        $owner = User::factory()->create(['balance' => 1000]);

        $order = Order::create([
            'user_id' => $owner->id,
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => 100,
            'amount' => 1,
            'status' => 1
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");
        
        $response->assertStatus(200);
        $this->assertEquals(3, $order->fresh()->status); // 3 = cancelled
    }
}
