<?php
namespace App\Repository\FrontEnd;

use App\Models\Order;

class OrderRepository
{
    public function getOrdersByUserId($userId)
    {
        return Order::where('user_id', $userId)->get();
    }
}
