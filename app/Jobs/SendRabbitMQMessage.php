<?php

namespace App\Jobs;

use App\Services\RabbitmqSendData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class SendRabbitMQMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct($data = null)
    {
        if ($data){
            $this->data = $data;
        }
    }

    /**
     * @throws \Exception
     */
    public function handle($data = null)
    {
        if ($data){
            $this->data = $data;
        }

        $this->delete();

        Queue::push(SendRabbitMQMessage::class, $data);

    }
}
