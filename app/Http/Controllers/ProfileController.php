<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('assets');
        
        $orders = Order::where('user_id', $user->id)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'balance' => $user->balance,
            ],
            'assets' => $user->assets,
            'orders' => $orders
        ]);
    }
}
