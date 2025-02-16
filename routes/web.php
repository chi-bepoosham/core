<?php

use App\Jobs\ProcessRabbitMQMessage;
use App\Jobs\SendRabbitMQMessage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'ChiBepoosham Api Services';
});

Route::get('/ok', function () {
    $data = [
        "action" => "process",
        "user_id" => "16161616",
        "image_link" => "https://test.com",
        "time" => \Carbon\Carbon::now()->format("H:i:s"),
    ];
    SendRabbitMQMessage::dispatch($data);

    return view('welcome');
});
