<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vision extends Model
{

    protected $fillable = [
        'current_count',
        'target_count',
        'is_active',
        'title',
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
            'current_count' => 'integer',
            'target_count' => 'integer',
            'is_active' => 'boolean',
            'title' => 'string',
            'description' => 'string',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
            'deleted_at' => 'timestamp',
        ];
    }
}
