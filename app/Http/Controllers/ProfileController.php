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
        
        $query = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('side') && $request->side !== 'all') {
            $query->where('side', $request->side);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 10);
        $orders = $query->paginate($perPage);
        
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
