<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

// use Modules\User\Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'birthday',
        'email',
        'email_verified_at',
        'avatar',
        'gender',
        'status',
        'body_image',
        'process_body_image_status',
        'error_body_image',
        'body_type_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'first_name' => 'string',
            'last_name' => 'string',
            'mobile' => 'string',
            'birthday' => 'date',
            'email' => 'string',
            'email_verified_at' => 'timestamp',
            'avatar' => 'string',
            'gender' => 'integer',
            'body_image' => 'string',
            'process_body_image_status' => 'integer',
            'error_body_image' => 'json',
            'status' => 'integer',
            'body_type_id' => 'integer',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
            'deleted_at' => 'timestamp',
        ];
    }

    public function bodyType(): BelongsTo
    {
        return $this->belongsTo(BodyType::class,"body_type_id");
    }

}
