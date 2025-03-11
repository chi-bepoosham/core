<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletTransaction extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'wallet_id',
        'type',
        'order_id',
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
            'wallet_id' => 'integer',
            'type' => 'string',
            'order_id' => 'integer',
            'amount' => 'integer',
            'date_time' => 'timestamp',
            'description' => 'string',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }

    public function wallet():BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

}
