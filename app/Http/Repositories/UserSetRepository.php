<?php

namespace App\Http\Repositories;


use App\Models\UserSet;

class UserSetRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return UserSet::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return ["clothes"];
    }
}
