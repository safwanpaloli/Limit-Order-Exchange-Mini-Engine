<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Support\Facades\Event;
use App\Events\OrderMatched;

class UserFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_end_to_end_order_matching_flow()
    {
        // 1. User setup
        $buyer = User::factory()->create(['balance' => 10000]);
        $seller = User::factory()->create(['balance' => 0]);
        
        $sellerAsset = Asset::create([
            'user_id' => $seller->id,
            'symbol' => 'BTC',
            'amount' => 1.0,
            'locked_amount' => 0
        ]);

        Event::fake([
            OrderMatched::class
        ]);

        // 2. Seller places a limit SELL order: 1 BTC @ 9500 USD
        $response1 = $this->actingAs($seller)->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'sell',
            'price' => 9500,
            'amount' => 1.0
        ]);
        
        $response1->assertStatus(201);
        
        // Verify seller's asset is locked
        $this->assertEquals(0, $seller->assets()->first()->amount);
        $this->assertEquals(1.0, $seller->assets()->first()->locked_amount);

        // 3. Buyer places a limit BUY order that crosses the spread: 1 BTC @ 9500 USD
        // Commission rule: 1.5%. Matched USD = 9500. 
        // Buyer gets crypto, fee taken from crypto = 0.015 BTC. Net crypto = 0.985 BTC.
        // Seller gets USD, fee taken from USD = 142.5 USD. Net USD = 9357.5 USD.
        $response2 = $this->actingAs($buyer)->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => 9500,
            'amount' => 1.0
        ]);

        $response2->assertStatus(201);

        // 4. Verify balances post-match
        $buyer->refresh();
        $seller->refresh();

        // Buyer paid 9500 USD upfront and got a 10000 - 9500 = 500 balance left
        $this->assertEquals(500, $buyer->balance);
        
        // Seller got 9500 USD minus 1.5% commission (142.5) = 9357.5 USD
        $this->assertEquals(9357.5, $seller->balance);

        // Verify Assets
        $buyerAsset = $buyer->assets()->where('symbol', 'BTC')->first();
        $sellerAsset = $seller->assets()->where('symbol', 'BTC')->first();

        // Buyer gets 1 BTC minus 1.5% fee = 0.985 BTC
        $this->assertEquals(0.985, $buyerAsset->amount);
        $this->assertEquals(0, $buyerAsset->locked_amount);

        // Seller asset is fully gone
        $this->assertEquals(0, $sellerAsset->amount);
        $this->assertEquals(0, $sellerAsset->locked_amount);

        // 5. Verify events broadcasted
        Event::assertDispatched(OrderMatched::class, function ($event) use ($buyer, $seller) {
            return in_array($event->userId, [$buyer->id, $seller->id]);
        });
    }
}
