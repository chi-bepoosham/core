<?php

use App\Jobs\SendRabbitMQMessage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $message =[
        "user_id"=>1212,
        "image_link"=>1212,
    ];
    SendRabbitMQMessage::dispatch($message);

    return view('welcome');
});
