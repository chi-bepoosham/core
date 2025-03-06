<?php

namespace App\Http\Repositories;


use App\Models\Order;

class OrderRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return Order::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return ['shop', 'user', 'userAddress', 'items', 'payment'];
    }
}
