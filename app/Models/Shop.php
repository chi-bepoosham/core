<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'uuid',
        'main_id',
        'province_id',
        'city_id',
        'address',
        'location_point',
        'manager_name',
        'manager_national_code',
        'mobile',
        'password',
        'brand_name',
        'description',
        'logo',
        'is_active',
        'is_verified',
        'phone',
        'web_site',
        'email',
        'shipping_fee',
        'card_number',
        'sheba_number',
        'commission_percent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'string',
            'uuid' => 'string',
            'main_id' => 'integer',
            'province_id' => 'integer',
            'city_id' => 'integer',
            'address' => 'string',
            'location_point' => 'string',
            'manager_name' => 'string',
            'manager_national_code' => 'string',
            'mobile' => 'string',
            'password' => 'string',
            'brand_name' => 'string',
            'description' => 'string',
            'logo' => 'string',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'phone' => 'string',
            'web_site' => 'string',
            'email' => 'string',
            'shipping_fee' => 'integer',
            'card_number' => 'string',
            'sheba_number' => 'string',
            'commission_percent' => 'float',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
            'deleted_at' => 'timestamp',
        ];
    }

    protected $hidden = ['password'];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
    public function city():BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function products():HasMany
    {
        return $this->hasMany(Product::class,'shop_id');
    }

    public function wallet():HasOne
    {
        return $this->hasOne(Wallet::class,'shop_id');
    }
}
