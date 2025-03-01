<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'user_id',
        'user_address_id',
        'delivery_type',
        'tracking_number',
        'status',
        'progress_status',
        'total_price',
        'discount',
        'vat',
        'shipping_fee',
        'final_price',
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
            'user_id' => 'integer',
            'user_address_id' => 'integer',
            'delivery_type' => 'string',
            'tracking_number' => 'string',
            'status' => 'string',
            'progress_status' => 'string',
            'total_price' => 'integer',
            'discount' => 'integer',
            'vat' => 'integer',
            'shipping_fee' => 'integer',
            'final_price' => 'integer',
            'description' => 'string',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
            'deleted_at' => 'timestamp',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id')->withTrashed();
    }

    public function userAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class,'order_id')->withTrashed();
    }

    public function payment(): HasOne
    {
        return $this->hasOne(OrderPayment::class,'order_id')->withTrashed();
    }
}
