<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessRabbitMQMessage implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
        Log::debug(json_encode($data));
    }

    public function handle()
    {
        // Here you can process the incoming message
        Log::info('Received RabbitMQ message', ['data' => $this->data]);

        // Example of accessing user_id and image_link
        $userId = $this->data['user_id'] ?? null;
        $imageLink = $this->data['image_link'] ?? null;

        // Process further based on your requirements
    }
}
