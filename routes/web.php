<?php


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
    \Illuminate\Support\Facades\Redis::publish(env('REDIS_PUBLISHER_QUEUE'), json_encode($data));

    return view('welcome');
});
