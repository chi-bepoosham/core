<?php

namespace App\Http\Repositories;


use App\Models\WalletTransaction;

class WalletTransactionRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return WalletTransaction::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return ["wallet"];
    }
}
