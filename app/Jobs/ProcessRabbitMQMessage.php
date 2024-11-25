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
            }else{
                $matchScore = $this->calculateScore($processImageData["process_data"]);
                $clothes = UserClothes::query()->find($clothesId);
                $clothes?->update(["processed_image_data" => json_encode($processImageData["process_data"]), "match_percentage" => $matchScore]);

            }
        }

    }

    public function calculateScore(array $imageData): int
    {
        $score = 0;

        if ($imageData['paintane'] === 'mpayintane' || $imageData['paintane'] === 'mbalatane') {
            if ($imageData['paintane'] === 'mbalatane') {
                $score += $this->menBalatane($imageData);
            } elseif ($imageData['paintane'] === 'mpayintane') {
                $score += $this->menPayintane($imageData);
            }
        } elseif ($imageData['paintane'] === 'fbalatane' || $imageData['paintane'] === 'fpayintane' || $imageData['paintane'] === 'ftamamtane') {
            if ($imageData['paintane'] === 'fbalatane') {
                $score += $this->womenBalatane($imageData);
            } elseif ($imageData['paintane'] === 'fpayintane' ||  $imageData['category'] === 'ftamamtane') {
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
        $collar = $data['collar'] ?? $data['yaghe'] ?? '';
        switch ($collar) {
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
        $sleeve = $data['sleeve'] ?? $data['astin'] ?? '';
        switch ($sleeve) {
            case 'shortsleeve':
                $score += 30;
                break;
            case 'sleeveless':
            case 'halfsleeve':
                $score += 10;
                break;
        }

        // Pattern
        $pattern = $data['pattern'] ?? '';
        switch ($pattern) {
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
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $score += (in_array('muted', $tones) || in_array('dark', $tones)) ? 10 : 0;
        $score += (in_array('bright', $tones) || in_array('light', $tones)) ? 0 : 0;

        return $score;
    }

    private function menPayintane(array $data): int
    {
        $score = 0;

        // Type
        $type = $data['type'] ?? $data['shalvar'] ?? '';
        switch ($type) {
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
        $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
        switch ($pattern) {
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
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $score += (in_array('muted', $tones) || in_array('dark', $tones)) ? 10 : 0;
        $score += (in_array('bright', $tones) || in_array('light', $tones)) ? 0 : 0;

        return $score;
    }

    private function womenBalatane(array $data): int
    {
        $score = 0;

        // Collar
        $collar = $data['collar'] ?? $data['yaghe'] ?? '';
        switch ($collar) {
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
        $sleeve = $data['sleeve'] ?? $data['astin'] ?? '';
        switch ($sleeve) {
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
        $silhouette = $data['silhouette'] ?? $data['skirt_type'] ?? '';
        switch ($silhouette) {
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

        if ($data['skirt_and_pants'] === 'skirt') {
            $type = $data['silhouette'] ?? $data['skirt_type'] ?? '';
            switch ($type) {
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
        } elseif ($data['skirt_and_pants'] === 'pants') {
            switch ($data['rise'] ?? '') {
                case 'highrise':
                case 'lowrise':
                    $score += 10;
                    break;
            }

            $type = $data['type'] ?? $data['shalvar'] ?? '';
            switch ($type) {
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
