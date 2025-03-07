<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * @property  $id
 * @property  $title
 * @property  $shop_id
 * @property  $category_id
 * @property  $main_id
 * @property  $color
 * @property  $gender
 * @property  $sizes
 * @property  $description
 * @property  $price
 * @property  $is_available
 * @property  $otherColors
 */
class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'shop_id',
        'category_id',
        'main_id',
        'color',
        'gender',
        'sizes',
        'description',
        'price',
        'is_available',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'title' => 'string',
            'shop_id' => 'integer',
            'category_id' => 'integer',
            'main_id' => 'integer',
            'color' => 'string',
            'gender' => 'string',
            'sizes' => 'array',
            'description' => 'string',
            'price' => 'integer',
            'is_available' => 'boolean',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
            'deleted_at' => 'timestamp',
        ];
    }

    protected $appends = ['other_colors'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class,'shop_id')->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class)->withTrashed();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }


    public function otherColors(): Attribute
    {
        return Attribute::get(function () {
            return
                DB::table($this->table)
                    ->select('id', 'color', 'main_id')
                    ->whereNot('id', $this->id)
                    ->when($this->main_id, function ($query) {
                        $query->where(function ($query) {
                            $query
                                ->where('main_id', $this->main_id)
                                ->orWhere('id', $this->main_id);
                        });
                    })
                    ->when(!$this->main_id, function ($query) {
                        $query->where('main_id', $this->id);
                    })
                    ->get();
        });
    }

    public function relatedProducts()
    {
        return $this->newQuery()
            ->with(['images', 'category'])
            ->whereNot('id', $this->id)
            ->when($this->main_id, function ($query) {
                $query->where(function ($query) {
                    $query
                        ->where('main_id', $this->main_id)
                        ->orWhere('id', $this->main_id);
                });
            })
            ->when(!$this->main_id, function ($query) {
                $query->where('main_id', $this->id);
            });
    }

}
