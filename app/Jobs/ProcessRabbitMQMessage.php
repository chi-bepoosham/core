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
        $action = $this->data['action'] ?? null;
        $userId = $this->data['user_id'] ?? null;
        $description = $this->data['description'] ?? null;

        Log::info('Action : '.$action);
        Log::info('User ID : '.$userId);
        Log::info('Description : '.$description);

    }
}
