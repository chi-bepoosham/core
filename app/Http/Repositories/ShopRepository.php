<?php

namespace App\Http\Repositories;


use App\Models\Shop;

class ShopRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return Shop::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return ['province', 'city'];
    }
}
