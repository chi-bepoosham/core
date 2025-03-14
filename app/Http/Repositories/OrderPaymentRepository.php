<?php

namespace App\Http\Repositories;


use App\Models\OrderPayment;

class OrderPaymentRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return OrderPayment::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return ['order'];
    }
}
