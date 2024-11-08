<?php

namespace App\Jobs;

use App\Http\Repositories\UserRepository;
use App\Models\BodyType;
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
        $gender = $this->data['gender'] ?? null;
        $clothesId = $this->data['clothes_id'] ?? null;
        $imageLink = $this->data['image_link'] ?? null;
        $time = $this->data['time'] ?? null;
        $processImageData = $this->data['process_image_data'] ?? null;

        Log::info('process Image : ', $processImageData);
        Log::info('action : ' . $action);
        Log::info('user_id : ' . $userId);
        Log::info('image Link : ' . $imageLink);
        Log::info('time : ' . $time);

        $userRepository = new UserRepository();
        $userItem = $userRepository->find($userId);
        if ($userItem != null) {
            if ($action == 'body_type') {
                $bodyType = BodyType::query()->where("predict_value", trim($processImageData["process_data"]))->first();
                if ($bodyType != null) {
                    $userRepository->update($userItem, [
                        "body_type_id" => $bodyType->id,
                        "process_body_image_status" => 2,
                    ]);
                }
            }
        }




    }
}
