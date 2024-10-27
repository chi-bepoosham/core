<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class TestRabbitMQ extends Command
{
    protected $signature = 'test:rabbitmq';
    protected $description = 'Test RabbitMQ connection';

    public function handle()
    {
        try {
            Queue::connection('rabbitmq')->pushRaw(json_encode(['test' => 'Hello RabbitMQ']), 'ai_predict_process');
            $this->info("Message sent successfully to ai_predict_process.");
        } catch (\Exception $e) {
            $this->error("Failed to connect: " . $e->getMessage());
        }
    }
}
