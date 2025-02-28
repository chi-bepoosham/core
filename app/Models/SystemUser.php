<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemUser extends Model
{
    protected $fillable = [
        'full_name',
        'username',
        'password',
        'status',
        'avatar',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'full_name' => 'string',
            'username' => 'string',
            'password' => 'string',
            'status' => 'boolean',
            'avatar' => 'string',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }

    protected $hidden = ['password'];


}
