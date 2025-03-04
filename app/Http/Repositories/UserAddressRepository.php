<?php

namespace App\Http\Repositories;


use App\Models\UserAddress;

class UserAddressRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return UserAddress::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return ['user', 'city', 'province'];
    }
}
