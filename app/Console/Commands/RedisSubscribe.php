<?php

namespace App\Console\Commands;

use App\Process\RedisMessageListener;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RedisSubscribe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to a Redis channel and listen for messages';


    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        set_time_limit(0);


        $channel = env('REDIS_SUBSCRIBER_CHANNEL');

        $this->info("Listening for messages on Redis channel: $channel");

        try {
            Redis::subscribe([$channel], function ($message) {
                (new RedisMessageListener(json_decode($message, true)))->handle();
            });
        } catch (\Throwable $exception) {
            $this->handle();
        }
    }
}
