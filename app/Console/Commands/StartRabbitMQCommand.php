<?php

namespace App\Console\Commands;

use App\Jobs\StartRabbitMQ;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class StartRabbitMQCommand extends Command
{
    protected $signature = 'start:rabbitmq';
    protected $description = 'Start RabbitMQ connection';

    public function handle()
    {
        try {
            StartRabbitMQ::dispatch();
            $this->info("Message sent successfully to ai_predict_process.");
        } catch (\Exception $e) {
            $this->error("Failed to connect: " . $e->getMessage());
        }
    }
}
