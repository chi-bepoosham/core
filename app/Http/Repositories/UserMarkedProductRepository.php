<?php

namespace App\Http\Repositories;


use App\Models\UserMarkedProduct;

class UserMarkedProductRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return UserMarkedProduct::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return ['product', 'shop'];
    }
}
