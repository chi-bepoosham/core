<?php

namespace App\Queue;

use App\Jobs\ProcessRabbitMQMessage;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob as BaseJob;

class CustomRabbitMQQueue extends BaseJob
{

    /**
     * Fire the job.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function fire()
    {
        $data = $this->payload();

        $class = ProcessRabbitMQMessage::class;
        $method = 'handle';

        ($this->instance = $this->resolve($class))->{$method}($data);

        $this->delete();
    }

    /**
     * Get the decoded body of the job.
     *
     * @return array
     */
    #[ArrayShape(['id' => "mixed", 'uuid' => "mixed", 'job' => "string", 'data' => "mixed"])]
    public function payload(): array
    {
        $payload = json_decode($this->getRawBody(), true);
        return [
            'id'  => $payload["id"],
            'uuid'  => $payload["uuid"],
            'job'  => ProcessRabbitMQMessage::class,
            'data' => $payload["data"]
        ];
    }
}
