<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CelebrityBodyType extends Model
{
    protected $fillable = [
        'title',
        'body_type_id',
        'image',
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
            'body_type_id' => 'integer',
            'image' => 'string',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }
}
