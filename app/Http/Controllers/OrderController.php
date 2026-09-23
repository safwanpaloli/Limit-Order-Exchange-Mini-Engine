<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\IndexOrderRequest;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrderController extends Controller
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function index(IndexOrderRequest $request)
    {
        $orders = $this->orderRepository->getOpenOrders($request->symbol);

        return response()->json($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderRepository->createOrder(Auth::user(), $request->validated());

            return response()->json([
                'message' => 'Order created successfully.',
                'order' => $order
            ], 201);
        } catch (Exception $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            if ($e->getMessage() === 'Insufficient USD balance.' || $e->getMessage() === 'Insufficient asset balance.') {
                $statusCode = 422;
            }
            return response()->json(['message' => $e->getMessage()], $statusCode);
        }
    }

    public function cancel(Order $order)
    {
        try {
            $cancelledOrder = $this->orderRepository->cancelOrder(Auth::user(), $order);

            return response()->json([
                'message' => 'Order cancelled successfully.',
                'order' => $cancelledOrder
            ]);
        } catch (Exception $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            if ($e->getMessage() === 'Only open orders can be cancelled.' || $e->getMessage() === 'Order is no longer open.') {
                $statusCode = 400;
            }
            return response()->json(['message' => $e->getMessage()], $statusCode);
        }
    }
}
