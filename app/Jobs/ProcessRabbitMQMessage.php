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
        $this->data = $data["data"];

        // Access specific fields from the payload
        $action = $this->data['action'] ?? null;
        $userId = $this->data['user_id'] ?? null;
        $gender = $this->data['gender'] ?? null;
        $clothesId = $this->data['clothes_id'] ?? null;
        $imageLink = $this->data['image_link'] ?? null;
        $time = $this->data['time'] ?? null;
        $processImageData = $this->data['process_image'] ?? null;

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

        $matchScore = $this->calculateScore($processImageData);
        $clothes = UserClothes::query()->find($clothesId);
        $clothes?->update(["processed_image_data" => json_encode($processImageData), "match_percentage" => $matchScore]);

    }

    public function calculateScore(array $imageData): int
    {
        $score = 0;

        if ($imageData['gender'] === 'male') {
            if (isset($imageData['category']) && $imageData['category'] === 'balatane') {
                $score += $this->menBalatane($imageData);
            } elseif (isset($imageData['category']) && $imageData['category'] === 'payintane') {
                $score += $this->menPayintane($imageData);
            }
        } elseif ($imageData['gender'] === 'female') {
            if (isset($imageData['category']) && $imageData['category'] === 'balatane') {
                $score += $this->womenBalatane($imageData);
            } elseif (isset($imageData['category']) && $imageData['category'] === 'payintane') {
                $score += $this->womenPayintane($imageData);
            }
        }

        // Ensure score does not exceed 100
        return min($score, 100);
    }

    private function menBalatane(array $data): int
    {
        $score = 0;

        // Collar
        switch ($data['collar'] ?? '') {
            case 'round':
            case 'classic':
            case 'turtleneck':
            case 'hoodie':
                $score += 30;
                break;
            case 'V_neck':
                $score += 10;
                break;
        }

        // Sleeve
        switch ($data['sleeve'] ?? '') {
            case 'shortsleeve':
                $score += 30;
                break;
            case 'sleeveless':
            case 'halfsleeve':
                $score += 10;
                break;
        }

        // Pattern
        switch ($data['pattern'] ?? '') {
            case 'dorosht':
            case 'rahrahofoghi':
                $score += 20;
                break;
            case 'sade':
                $score += 10;
                break;
            case 'riz':
            case 'rahrahamudi':
                $score += 0;
                break;
        }

        // Color
        switch ($data['color'] ?? '') {
            case 'light':
            case 'bright':
                $score += 10;
                break;
            case 'dark':
            case 'muted':
                $score += 0;
                break;
        }

        return $score;
    }

    private function menPayintane(array $data): int
    {
        $score = 0;

        // Type
        switch ($data['type'] ?? '') {
            case 'mstraight':
            case 'mslimfit':
                $score += 40;
                break;
            case 'mshorts':
            case 'mmom':
                $score += 20;
                break;
            case 'mbaggy':
            case 'mcargo':
            case 'mcargoshorts':
                $score += 0;
                break;
        }

        // Pattern
        switch ($data['pattern'] ?? '') {
            case 'mpamudi':
            case 'mpriz':
            case 'mpsade':
                $score += 40;
                break;
            case 'mpofoghi':
            case 'mpdorosht':
                $score += 0;
                break;
        }

        // Color
        switch ($data['color'] ?? '') {
            case 'dark':
            case 'muted':
                $score += 10;
                break;
            case 'light':
            case 'bright':
                $score += 0;
                break;
        }

        return $score;
    }

    private function womenBalatane(array $data): int
    {
        $score = 0;

        // Collar
        switch ($data['collar'] ?? '') {
            case 'off_the_shoulder':
            case 'V_neck':
            case 'squer':
            case 'sweatheart':
                $score += 20;
                break;
            case 'turtleneck':
            case 'round':
            case 'one_shoulder':
            case 'halter':
            case 'boatneck':
            case 'hoodie':
            case 'classic':
                $score += 10;
                break;
        }

        // Sleeve
        switch ($data['sleeve'] ?? '') {
            case 'fsleeveless':
            case 'fhalfsleeve':
            case 'bottompuffy':
                $score += 20;
                break;
            case 'fshortsleeve':
            case 'flongsleeve':
                $score += 10;
                break;
            case 'toppuffy':
                $score += 0;
                break;
        }

        // Silhouette
        switch ($data['silhouette'] ?? '') {
            case 'snatched':
            case 'wrap':
            case 'peplum':
            case 'belted':
                $score += 5;
                break;
            case 'cowl':
            case 'empire':
                $score += 3;
                break;
            case 'loose':
                $score += 0;
                break;
        }

        // Pattern
        $score += 10; // Default score for pattern

        // Color
        $score += 10; // Default score for color

        return $score;
    }

    private function womenPayintane(array $data): int
    {
        $score = 0;

        if ($data['kind'] === 'skirt') {
            switch ($data['type'] ?? '') {
                case 'wrapskirt':
                case 'balloonskirt':
                case 'mermaidskirt':
                case 'alineskirt':
                case 'pencilskirt':
                case 'miniskirt':
                case 'shortaskirt':
                    $score += 20;
                    break;
            }
        } elseif ($data['kind'] === 'pants') {
            switch ($data['rise'] ?? '') {
                case 'highrise':
                case 'lowrise':
                    $score += 10;
                    break;
            }

            switch ($data['type'] ?? '') {
                case 'wbaggy':
                case 'wstraight':
                case 'wskinny':
                case 'wbootcut':
                case 'wcargo':
                case 'wshorts':
                case 'wcargoshorts':
                case 'wmom':
                    $score += 10;
                    break;
            }
        }

        // Pattern
        $score += 10; // Default for skirt/pants pattern

        // Color
        $score += 10; // Default for color

        return $score;
    }

}
