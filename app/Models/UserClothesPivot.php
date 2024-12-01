<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserClothesPivot extends Model
{
    protected $table = 'user_clothes_pivot';

    protected $fillable = [
        'first_user_clothes_id',
        'second_user_clothes_id',
        'matched',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'first_user_clothes_id' => 'integer',
            'second_user_clothes_id' => 'integer',
            'matched' => 'boolean',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }

}
