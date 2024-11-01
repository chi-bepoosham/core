<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessRabbitMQMessage implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public $data;

    public function handle($data)
    {
        $this->data = $data["data"];

        // Access specific fields from the payload
        $processImage = $this->data['process_image'] ?? null;
        $action = $this->data['action'] ?? null;
        $uuid = $this->data['uuid'] ?? null;
        $user_id = $this->data['user_id'] ?? null;
        $imageLink = $this->data['image_link'] ?? null;
        $time = $this->data['time'] ?? null;

        Log::info('process Image : ', $processImage);
        Log::info('action : ' . $action);
        Log::info('uuid : ' . $uuid);
        Log::info('user_id : ' . $user_id);
        Log::info('image Link : ' . $imageLink);
        Log::info('time : ' . $time);

    }
}
