<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\Order;
use App\Models\Asset;
use App\Models\Trade;
use App\Events\OrderbookUpdated;
use App\Events\WalletUpdated;
use App\Events\OrderMatched;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderRepository implements OrderRepositoryInterface
{
    public function getOpenOrders(string $symbol): array
    {
        $orders = Order::where('symbol', $symbol)
            ->where('status', 1)
            ->orderBy('price', 'desc')
            ->get();

        return [
            'buy' => $orders->where('side', 'buy')->values(),
            'sell' => $orders->where('side', 'sell')->values()
        ];
    }

    public function createOrder(User $user, array $data): Order
    {
        $symbol = $data['symbol'];
        $side = $data['side'];
        $price = $data['price'];
        $amount = $data['amount'];
        $totalCost = $price * $amount;

        return DB::transaction(function () use ($user, $symbol, $side, $price, $amount, $totalCost) {
            $lockedUser = DB::table('users')->where('id', $user->id)->lockForUpdate()->first();

            if ($side === 'buy') {
                if ($lockedUser->balance < $totalCost) {
                    throw new Exception('Insufficient USD balance.');
                }
                
                DB::table('users')->where('id', $user->id)->update([
                    'balance' => $lockedUser->balance - $totalCost
                ]);
            } else {
                $asset = Asset::where('user_id', $user->id)
                    ->where('symbol', $symbol)
                    ->lockForUpdate()
                    ->first();

                if (!$asset || $asset->amount < $amount) {
                    throw new Exception('Insufficient asset balance.');
                }

                $asset->amount -= $amount;
                $asset->locked_amount += $amount;
                $asset->save();
            }

            $order = Order::create([
                'user_id' => $user->id,
                'symbol' => $symbol,
                'side' => $side,
                'price' => $price,
                'amount' => $amount,
                'status' => 1
            ]);

            $this->matchOrder($order);

            event(new OrderbookUpdated($symbol));
            event(new WalletUpdated($user->id));

            return $order;
        });
    }

    public function cancelOrder(User $user, Order $order): Order
    {
        if ($order->user_id !== $user->id) {
            throw new Exception('Unauthorized.', 403);
        }

        if ($order->status !== 1) {
            throw new Exception('Only open orders can be cancelled.', 400);
        }

        return DB::transaction(function () use ($user, $order) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
            
            if ($lockedOrder->status !== 1) {
                throw new Exception('Order is no longer open.', 400);
            }

            if ($lockedOrder->side === 'buy') {
                $totalCost = $lockedOrder->price * $lockedOrder->amount;
                $lockedUser = DB::table('users')->where('id', $user->id)->lockForUpdate()->first();
                
                DB::table('users')->where('id', $user->id)->update([
                    'balance' => $lockedUser->balance + $totalCost
                ]);
            } else {
                $asset = Asset::where('user_id', $user->id)
                    ->where('symbol', $lockedOrder->symbol)
                    ->lockForUpdate()
                    ->first();
                    
                $asset->amount += $lockedOrder->amount;
                $asset->locked_amount -= $lockedOrder->amount;
                $asset->save();
            }

            $lockedOrder->status = 3; // 3 = Cancelled
            $lockedOrder->save();

            event(new OrderbookUpdated($lockedOrder->symbol));
            event(new WalletUpdated($user->id));

            return $lockedOrder;
        });
    }

    protected function matchOrder(Order $takerOrder)
    {
        $remainingAmount = $takerOrder->amount;

        $query = Order::where('symbol', $takerOrder->symbol)
            ->where('status', 1)
            ->where('side', $takerOrder->side === 'buy' ? 'sell' : 'buy');

        if ($takerOrder->side === 'buy') {
            $query->where('price', '<=', $takerOrder->price)
                  ->orderBy('price', 'asc');
        } else {
            $query->where('price', '>=', $takerOrder->price)
                  ->orderBy('price', 'desc');
        }
        
        $query->orderBy('created_at', 'asc'); // FIFO

        $makerOrders = $query->lockForUpdate()->get();

        foreach ($makerOrders as $makerOrder) {
            if ($remainingAmount <= 0) break;

            $fillAmount = min($remainingAmount, $makerOrder->amount);
            $executionPrice = $makerOrder->price;

            // Execute Trade
            Trade::create([
                'maker_order_id' => $makerOrder->id,
                'taker_order_id' => $takerOrder->id,
                'symbol' => $takerOrder->symbol,
                'price' => $executionPrice,
                'amount' => $fillAmount
            ]);

            // Settlement
            $this->settleTrade($takerOrder, $makerOrder, $fillAmount, $executionPrice);

            // Update maker order
            $makerOrder->amount -= $fillAmount;
            if ($makerOrder->amount <= 0) {
                $makerOrder->status = 2; // Filled
            }
            $makerOrder->save();

            $remainingAmount -= $fillAmount;
        }

        // Update taker order
        if ($remainingAmount < $takerOrder->amount) {
            $takerOrder->amount = $remainingAmount;
            if ($remainingAmount <= 0) {
                $takerOrder->status = 2; // Filled
            }
            $takerOrder->save();
        }
    }

    protected function settleTrade(Order $takerOrder, Order $makerOrder, $fillAmount, $executionPrice)
    {
        $takerUser = DB::table('users')->where('id', $takerOrder->user_id)->lockForUpdate()->first();
        $makerUser = DB::table('users')->where('id', $makerOrder->user_id)->lockForUpdate()->first();
        
        $grossUsdValue = $fillAmount * $executionPrice;

        if ($takerOrder->side === 'buy') {
            // Taker is Buyer: pays fiat (already deducted upfront), gets crypto
            // Maker is Seller: pays crypto (already locked), gets fiat
            
            $makerFeeUsd = $grossUsdValue * 0.015;
            $makerNetUsd = $grossUsdValue - $makerFeeUsd;
            
            $takerFeeCrypto = $fillAmount * 0.015;
            $takerNetCrypto = $fillAmount - $takerFeeCrypto;

            // Maker gets fiat (net of fee)
            DB::table('users')->where('id', $makerUser->id)->update([
                'balance' => $makerUser->balance + $makerNetUsd
            ]);

            // Maker's crypto is unlocked and permanently removed
            $makerAsset = Asset::where('user_id', $makerUser->id)
                ->where('symbol', $takerOrder->symbol)
                ->lockForUpdate()
                ->first();
            $makerAsset->locked_amount -= $fillAmount;
            $makerAsset->save();

            // Taker gets crypto (net of fee)
            $takerAsset = Asset::where('user_id', $takerUser->id)
                ->where('symbol', $takerOrder->symbol)
                ->lockForUpdate()
                ->first();
                
            if (!$takerAsset) {
                $takerAsset = Asset::create([
                    'user_id' => $takerUser->id, 
                    'symbol' => $takerOrder->symbol, 
                    'amount' => 0, 
                    'locked_amount' => 0
                ]);
            }
            $takerAsset->amount += $takerNetCrypto;
            $takerAsset->save();

            // Refund for Price Improvement
            $priceDiff = $takerOrder->price - $executionPrice;
            if ($priceDiff > 0) {
                DB::table('users')->where('id', $takerUser->id)->update([
                    'balance' => DB::raw("balance + " . ($fillAmount * $priceDiff))
                ]);
            }
            
            // Dispatch OrderMatched Events
            event(new OrderMatched($takerOrder->user_id, [
                'symbol' => $takerOrder->symbol,
                'side' => 'buy',
                'price' => $executionPrice,
                'amount' => $fillAmount,
                'fee' => $takerFeeCrypto . ' ' . $takerOrder->symbol
            ]));
            
            event(new OrderMatched($makerOrder->user_id, [
                'symbol' => $makerOrder->symbol,
                'side' => 'sell',
                'price' => $executionPrice,
                'amount' => $fillAmount,
                'fee' => $makerFeeUsd . ' USD'
            ]));

        } else {
            // Taker is Seller: pays crypto (already locked upfront), gets fiat
            // Maker is Buyer: pays fiat (already deducted), gets crypto

            $takerFeeUsd = $grossUsdValue * 0.015;
            $takerNetUsd = $grossUsdValue - $takerFeeUsd;
            
            $makerFeeCrypto = $fillAmount * 0.015;
            $makerNetCrypto = $fillAmount - $makerFeeCrypto;

            // Taker gets fiat (net of fee)
            DB::table('users')->where('id', $takerUser->id)->update([
                'balance' => $takerUser->balance + $takerNetUsd
            ]);

            // Taker's crypto is unlocked and permanently removed
            $takerAsset = Asset::where('user_id', $takerUser->id)
                ->where('symbol', $takerOrder->symbol)
                ->lockForUpdate()
                ->first();
            $takerAsset->locked_amount -= $fillAmount;
            $takerAsset->save();

            // Maker gets crypto (net of fee)
            $makerAsset = Asset::where('user_id', $makerUser->id)
                ->where('symbol', $takerOrder->symbol)
                ->lockForUpdate()
                ->first();
                
            if (!$makerAsset) {
                $makerAsset = Asset::create([
                    'user_id' => $makerUser->id, 
                    'symbol' => $takerOrder->symbol, 
                    'amount' => 0, 
                    'locked_amount' => 0
                ]);
            }
            $makerAsset->amount += $makerNetCrypto;
            $makerAsset->save();
            
            // Refund for Price Improvement for Maker (not applicable since Maker set the price, but Taker might sell lower than Maker's bid)
            // Wait, if Taker sells lower than Maker's bid, the execution price IS the Maker's bid.
            // So no refund needed.

            // Dispatch OrderMatched Events
            event(new OrderMatched($takerOrder->user_id, [
                'symbol' => $takerOrder->symbol,
                'side' => 'sell',
                'price' => $executionPrice,
                'amount' => $fillAmount,
                'fee' => $takerFeeUsd . ' USD'
            ]));
            
            event(new OrderMatched($makerOrder->user_id, [
                'symbol' => $makerOrder->symbol,
                'side' => 'buy',
                'price' => $executionPrice,
                'amount' => $fillAmount,
                'fee' => $makerFeeCrypto . ' ' . $makerOrder->symbol
            ]));
        }

        event(new WalletUpdated($takerOrder->user_id));
        event(new WalletUpdated($makerOrder->user_id));
    }
}
