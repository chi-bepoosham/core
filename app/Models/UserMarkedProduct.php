<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMarkedProduct extends Model
{
    protected $fillable = [
        'user_id',
        'shop_id',
        'product_id',
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
            'shop_id' => 'integer',
            'product_id' => 'integer',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class,'shop_id')->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function product(): BelongsTo{
        return $this->belongsTo(Product::class,'product_id')->with(['images','shop']);
    }


}
