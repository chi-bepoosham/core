<?php

namespace App\Http\Repositories;


use App\Models\SystemUser;

class SystemUserRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return SystemUser::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return [];
    }
}
