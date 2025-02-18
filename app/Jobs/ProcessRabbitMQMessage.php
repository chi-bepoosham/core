<?php

namespace App\Jobs;

use App\Http\Repositories\UserRepository;
use App\Models\BodyType;
use App\Models\UserClothes;
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
    }


}
