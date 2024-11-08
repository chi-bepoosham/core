<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BodyType extends Model
{
    protected $fillable = [
        'title',
        'gender',
        'predict_value',
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
            'gender' => 'integer',
            'predict_value' => 'string',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }

    public function celebrities(): HasMany
    {
        return $this->hasMany(CelebrityBodyType::class,"body_type_id");
    }

    public function clothes(): HasMany
    {
        return $this->hasMany(ClothesBodyType::class,"body_type_id");
    }
}
