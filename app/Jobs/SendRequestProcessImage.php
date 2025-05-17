<?php

namespace App\Jobs;

use App\Process\RedisMessageListener;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendRequestProcessImage implements ShouldQueue
{
    use Queueable, Dispatchable;

    public array $data;
    public string $type;
    public ?int $userId;
    public ?int $clothesId;
    public string $serviceEndPoint= 'model_handler:5001';
    public string $serviceUrl;
    public int $timeout = 300;
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $type, $userId = null, $clothesId = null)
    {
        $this->data = $data;
        $this->type = $type;
        $this->userId = $userId;
        $this->clothesId = $clothesId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->type == 'bodyType'){
            $this->serviceUrl = $this->serviceEndPoint . '/bodytype';
        }else{
            $this->serviceUrl = $this->serviceEndPoint . '/clothing';
        }


        try {
            $response = Http::timeout(180)->post($this->serviceUrl, $this->data);
            $data["result"] = $response->json();
            $data["user_id"] = $this->userId;
            $data["clothes_id"] = $this->clothesId;
            $data["category"] = $this->type;
            (new RedisMessageListener($data))->handle();
        }catch (Exception $e){
            throw $e;
        }

    }
}
