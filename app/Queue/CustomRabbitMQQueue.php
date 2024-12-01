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

        if (count($data) == 0) {
            $this->delete();
            return;
        }

        $class = $data["job"] ?? null;
        if (!$class) {
            $this->delete();
            return;
        }

        $method = 'handle';
        $this->delete();

        ($this->instance = $this->resolve($class))->{$method}($data);

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
        if (isset($payload["displayName"])){
            $payload["job"] = $payload["displayName"];
            return $payload;
        }

        if (isset($payload["id"])){
            return [
                'id'  => $payload["id"],
                'uuid'  => $payload["uuid"],
                'job'  => ProcessRabbitMQMessage::class,
                'data' => $payload["data"]
            ];
        }

        return [];
    }
}
