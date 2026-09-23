<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\Order;
use App\Models\Asset;
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

            return Order::create([
                'user_id' => $user->id,
                'symbol' => $symbol,
                'side' => $side,
                'price' => $price,
                'amount' => $amount,
                'status' => 1
            ]);
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

            $lockedOrder->status = 3;
            $lockedOrder->save();

            return $lockedOrder;
        });
    }
}
