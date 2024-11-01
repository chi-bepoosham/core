<?php

namespace App\Services;

use App\Jobs\SendRabbitMQMessage;
use Illuminate\Support\Facades\Queue;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitmqSendData
{
    /**
     * @throws \Exception
     */
    public function send($data)
    {
    }
}
