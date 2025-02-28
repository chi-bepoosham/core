<?php

namespace App\Http\Repositories;


use App\Models\Product;

class ProductRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return Product::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return ['category', 'images'];
    }
}
