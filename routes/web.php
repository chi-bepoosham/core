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
    \App\Jobs\SendRedisMessage::dispatch($data);

    return view('welcome');
});
