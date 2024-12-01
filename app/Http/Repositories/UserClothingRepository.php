<?php

namespace App\Http\Repositories;


use App\Models\UserClothes;

class UserClothingRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return UserClothes::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return ["matchedClothing"];
    }
}
