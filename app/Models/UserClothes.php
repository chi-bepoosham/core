<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class UserClothes extends Model
{
    protected $table = 'user_clothes';

    protected $fillable = [
        'image',
        'match_percentage',
        'clothes_type',
        'process_status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'image' => 'string',
            'match_percentage' => 'integer',
            'clothes_type' => 'integer',
            'process_status' => 'integer',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }

    public function matchedClothing():BelongsToMany
    {
        return $this->belongsToMany(UserClothes::class,"user_clothes_pivot","first_user_clothes_id","second_user_clothes_id")->withPivot('matched');
    }
}
