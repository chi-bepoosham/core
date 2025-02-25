<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserClothesSet extends Model
{
    protected $table = 'user_clothes_sets';

    protected $fillable = [
        'user_set_id',
        'user_clothe_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_set_id' => 'integer',
            'user_clothe_id' => 'integer',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }

}
