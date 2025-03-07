<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBodyTypeHistory extends Model
{
    protected $table = 'user_body_type_histories';

    protected $fillable = [
        'user_id',
        'body_image',
        'user_data',
    ];

}
