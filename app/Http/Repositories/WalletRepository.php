<?php

namespace App\Http\Repositories;


use App\Models\Wallet;

class WalletRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return Wallet::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return ["transactions"];
    }
}
