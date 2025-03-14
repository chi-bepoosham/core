<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSet extends Model
{
    use SoftDeletes;

    protected $table = 'user_sets';

    protected $fillable = [
        'user_id',
        'title',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'title' => 'string',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
            'deleted_at' => 'timestamp',
        ];
    }

    public function clothes(): BelongsToMany
    {
        return $this->belongsToMany(UserClothes::class, 'user_clothes_sets','user_set_id','user_clothe_id');
    }

}
