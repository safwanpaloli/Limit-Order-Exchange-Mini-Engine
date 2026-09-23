<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use App\Models\Order;

interface OrderRepositoryInterface
{
    public function getOpenOrders(string $symbol): array;
    
    public function createOrder(User $user, array $data): Order;
    
    public function cancelOrder(User $user, Order $order): Order;
}
