<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revenues extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'wallet_transaction_id',
        'type',
        'amount',
        'date_time',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'wallet_transaction_id' => 'integer',
            'type' => 'string',
            'amount' => 'integer',
            'date_time' => 'timestamp',
            'description' => 'string',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
            'deleted_at' => 'timestamp',
        ];
    }

    public function walletTransaction():BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class)->withTrashed()->with('order');
    }
}
