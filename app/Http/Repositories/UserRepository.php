<?php

namespace App\Http\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return User::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return [];
    }
}
