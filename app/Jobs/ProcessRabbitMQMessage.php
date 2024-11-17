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


    }

    public function calculateScore(array $imageData): int
    {
        $score = 0;

        // Women balatane
        if (in_array($imageData['paintane'] ?? null, ['fbalatane', 'ftamamtane'])) {
            if (!empty($imageData['astin'])) {
                $score += $imageData['astin'] === 'flongsleeve' ? 20 : 10;
            }
            if (!empty($imageData['pattern'])) {
                $score += $imageData['pattern'] !== 'sade' ? 10 : 5;
            }
            if (!empty($imageData['yaghe'])) {
                $score += in_array($imageData['yaghe'], ['round', 'v_neck']) ? 20 : 10;
            }

            // Silhouette features
            $silhouetteFeatures = ['balted', 'cowl', 'empire', 'loose', 'snatched', 'wrap', 'peplum'];
            foreach ($silhouetteFeatures as $feature) {
                if (!empty($imageData[$feature])) {
                    if ($imageData[$feature] == $feature){
                        if ($feature == 'loose' || $feature == 'snatched') {
                            $score += 5;
                        } else {
                            $score += 3;
                        }
                    }
                }
            }


            // Color tone
            if (!empty($imageData['color_tone'])) {
                $tones = explode('_', $imageData['color_tone']);
                $score += (in_array('light', $tones) || in_array('dark', $tones)) ? 5 : 3;
                $score += (in_array('bright', $tones) || in_array('muted', $tones)) ? 5 : 3;
            }
        }

        // Women paintane skirt/pants
        if (in_array($imageData['paintane'] ?? null, ['fpaintane', 'ftamamtane'])) {
            if (!empty($imageData['rise'])) {
                $score += $imageData['rise'] === 'highrise' ? 30 : 10;
            }
            if (!empty($imageData['shalvar'])) {
                $score += $imageData['shalvar'] === 'wskinny' ? 30 : 10;
            }
            if (!empty($imageData['tarh_shalvar'])) {
                $score += $imageData['tarh_shalvar'] !== 'wpsade' ? 20 : 10;
            }
            if (!empty($imageData['skirt_and_pants']) && $imageData['skirt_and_pants'] === 'skirt') {
                if (!empty($imageData['skirt_print'])) {
                    $score += $imageData['skirt_print'] !== 'skirtsade' ? 40 : 20;
                }
                if (!empty($imageData['skirt_type'])) {
                    $score += $imageData['skirt_type'] === 'wrapskirt' ? 40 : 20;
                }
            }

            // Color tone
            if (!empty($imageData['color_tone'])) {
                $tones = explode('_', $imageData['color_tone']);
                $score += (in_array('light', $tones) || in_array('dark', $tones)) ? 10 : 5;
                $score += (in_array('bright', $tones) || in_array('muted', $tones)) ? 10 : 5;
            }
        }


        // Men balatane
        if ($imageData['paintane'] ?? null === 'mbalatane') {
            if (!empty($imageData['astin'])) {
                $score += $imageData['astin'] === 'flongsleeve' ? 15 : 10;
            }
            if (!empty($imageData['pattern'])) {
                $score += $imageData['pattern'] === 'riz' ? 10 : 5;
            }
            if (!empty($imageData['yaghe'])) {
                $score += $imageData['yaghe'] === 'one_shoulder' ? 20 : 10;
            }

            // Color tone
            if (!empty($imageData['color_tone'])) {
                $tones = explode('_', $imageData['color_tone']);
                $score += (in_array('light', $tones) || in_array('dark', $tones)) ? 10 : 5;
                $score += (in_array('bright', $tones) || in_array('muted', $tones)) ? 10 : 5;
            }
        }

        // Men paintane pants
        if ($imageData['paintane'] ?? null === 'mpayintane') {
            if (!empty($imageData['rise'])) {
                $score += $imageData['rise'] === 'lowrise' ? 10 : 5;
            }
            if (!empty($imageData['shalvar'])) {
                $score += $imageData['shalvar'] === 'mbaggy' ? 20 : 10;
            }
            if (!empty($imageData['tarh_shalvar'])) {
                $score += $imageData['tarh_shalvar'] === 'mpamudi' ? 20 : 10;
            }
            if (!empty($imageData['skirt_and_pants']) && $imageData['skirt_and_pants'] === 'pants') {
                $score += 10;
            }

            // Color tone
            if (!empty($imageData['color_tone'])) {
                $tones = explode('_', $imageData['color_tone']);
                $score += (in_array('light', $tones) || in_array('dark', $tones)) ? 10 : 5;
                $score += (in_array('bright', $tones) || in_array('muted', $tones)) ? 10 : 5;
            }
        }


        return $score > 100 ? 100 : $score;
    }
}
