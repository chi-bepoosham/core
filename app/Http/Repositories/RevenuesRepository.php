<?php

namespace App\Http\Repositories;


use App\Models\Revenues;

class RevenuesRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return Revenues::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return ["walletTransaction"];
    }
}
