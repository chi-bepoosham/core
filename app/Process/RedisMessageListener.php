<?php

namespace App\Process;

use App\Http\Repositories\UserRepository;
use App\Models\BodyType;
use App\Models\UserClothes;
use Illuminate\Support\Facades\Log;

class RedisMessageListener
{

    public array $data;

    public function __construct(array $inputs)
    {
        $this->data = $inputs;
    }

    /**
     * Handle the event.
     */
    public function handle(): void
    {
        $data = $this->data;

        // Access specific fields from the payload
        $category = $data['category'] ?? null;
        $userId = $data['user_id'] ?? null;
        $clothesId = $data['clothes_id'] ?? null;
        $processImageData = $data['result'] ?? null;


        Log::info('process Image : ', $processImageData);
        Log::info('category : ' . $category);
        Log::info('user_id : ' . $userId);
        Log::info('----------------------------');


        $userRepository = new UserRepository();
        $userItem = $userRepository->find($userId);
        if ($userItem != null) {
            if ($category == 'bodyType') {
                if (isset($processImageData["data"]) && $processImageData["data"] != null) {
                    $bodyType = BodyType::query()->where("predict_value", trim($processImageData["data"]["body_type"]))->first();
                    if ($bodyType != null) {
                        $userRepository->update($userItem, [
                            "body_type_id" => $bodyType->id,
                            "process_body_image_status" => 2,
                            "error_body_image" => null,
                        ]);
                    }
                } else {
                    $userRepository->update($userItem, [
                        "process_body_image_status" => 3,
                        "error_body_image" => json_encode($processImageData["error"]),
                    ]);
                }
            } else {
                if (isset($processImageData["data"]) && $processImageData["data"] != null) {
                    $userItemBodyType = $userItem?->bodyType?->predict_value ?? null;

                    if ($userItemBodyType != null) {
                        try {
                            $clothesType = $this->getClothesType($processImageData["data"]);
                            $matchScore = $this->calculateScore($processImageData["data"], trim($userItemBodyType));
                            $clothes = UserClothes::query()->find($clothesId);
                            if ($clothes != null) {
                                $clothes->update(["process_status" => 2, "processed_image_data" => json_encode($processImageData["data"]), "match_percentage" => $matchScore, "clothes_type" => $clothesType]);
                                sleep(2);
                                $clothes->matchWithOtherClothes();
                            }

                        } catch (\Exception $exception) {
                            Log::debug("Error On Update : --------------------------------");
                            Log::debug($exception->getMessage());
                        }
                    }
                } else {
                    $clothes = UserClothes::query()->find($clothesId);
                    if ($clothes != null) {
                        $clothes->update([
                            "process_status" => 3,
                            "error_clothes" => json_encode($processImageData["error"]),
                        ]);
                    }
                }
            }
        }
    }


    public function getClothesType(array $imageData): int
    {
        if ($imageData['paintane'] === 'ftamamtane') {
            return 3;
        } elseif ($imageData['paintane'] === 'mpayintane' || $imageData['paintane'] === 'fpayintane') {
            return 2;
        } else {
            return 1;
        }
    }

    public function calculateScore(array $imageData, $userBodyType): int
    {
        $score = 0;
        Log::info('--- Calculate Score Start ---');
        Log::info('User Body Type: ' . $userBodyType);
        Log::info('Image Data: ' . json_encode($imageData));

        if ($imageData['paintane'] === 'mpayintane' || $imageData['paintane'] === 'mbalatane') {
            if ($imageData['paintane'] === 'mbalatane') {
                switch ((int)$userBodyType) {
                    case 0:
                        Log::info('Before menBalataneZero: score=' . $score);
                        $score += $this->menBalataneZero($imageData);
                        Log::info('After menBalataneZero: score=' . $score);
                        break;
                    case 1:
                        Log::info('Before menBalataneTwo: score=' . $score);
                        $score += $this->menBalataneTwo($imageData);
                        Log::info('After menBalataneTwo: score=' . $score);
                        break;
                    case 2:
                        Log::info('Before menBalataneFive: score=' . $score);
                        $score += $this->menBalataneFive($imageData);
                        Log::info('After menBalataneFive: score=' . $score);
                        break;
                }
            } elseif ($imageData['paintane'] === 'mpayintane') {
                switch ((int)$userBodyType) {
                    case 0:
                        Log::info('Before menPayintaneZero: score=' . $score);
                        $score += $this->menPayintaneZero($imageData);
                        Log::info('After menPayintaneZero: score=' . $score);
                        break;
                    case 1:
                        Log::info('Before menPayintaneTwo: score=' . $score);
                        $score += $this->menPayintaneTwo($imageData);
                        Log::info('After menPayintaneTwo: score=' . $score);
                        break;
                    case 2:
                        Log::info('Before menPayintaneFive: score=' . $score);
                        $score += $this->menPayintaneFive($imageData);
                        Log::info('After menPayintaneFive: score=' . $score);
                        break;
                }
            }
        } elseif ($imageData['paintane'] === 'fbalatane' || $imageData['paintane'] === 'fpayintane' || $imageData['paintane'] === 'ftamamtane') {
            if ($imageData['paintane'] === 'fbalatane') {
                switch ((int)$userBodyType) {
                    case 11:
                        Log::info('Before womenBalataneOneOne: score=' . $score);
                        $score += $this->womenBalataneOneOne($imageData);
                        Log::info('After womenBalataneOneOne: score=' . $score);
                        break;
                    case 21:
                        Log::info('Before womenBalataneTwoOne: score=' . $score);
                        $score += $this->womenBalataneTwoOne($imageData);
                        Log::info('After womenBalataneTwoOne: score=' . $score);
                        break;
                    case 31:
                        Log::info('Before womenBalataneThreeOne: score=' . $score);
                        $score += $this->womenBalataneThreeOne($imageData);
                        Log::info('After womenBalataneThreeOne: score=' . $score);
                        break;
                    case 41:
                        Log::info('Before womenBalataneFourOne: score=' . $score);
                        $score += $this->womenBalataneFourOne($imageData);
                        Log::info('After womenBalataneFourOne: score=' . $score);
                        break;
                    case 51:
                        Log::info('Before womenBalataneFiveOne: score=' . $score);
                        $score += $this->womenBalataneFiveOne($imageData);
                        Log::info('After womenBalataneFiveOne: score=' . $score);
                        break;
                }
            } else {
                switch ((int)$userBodyType) {
                    case 11:
                        Log::info('Before womenPayintaneOneOne: score=' . $score);
                        $score += $this->womenPayintaneOneOne($imageData);
                        Log::info('After womenPayintaneOneOne: score=' . $score);
                        break;
                    case 21:
                        Log::info('Before womenPayintaneTwoOne: score=' . $score);
                        $score += $this->womenPayintaneTwoOne($imageData);
                        Log::info('After womenPayintaneTwoOne: score=' . $score);
                        break;
                    case 31:
                        Log::info('Before womenPayintaneThreeOne: score=' . $score);
                        $score += $this->womenPayintaneThreeOne($imageData);
                        Log::info('After womenPayintaneThreeOne: score=' . $score);
                        break;
                    case 41:
                        Log::info('Before womenPayintaneFourOne: score=' . $score);
                        $score += $this->womenPayintaneFourOne($imageData);
                        Log::info('After womenPayintaneFourOne: score=' . $score);
                        break;
                    case 51:
                        Log::info('Before womenPayintaneFiveOne: score=' . $score);
                        $score += $this->womenPayintaneFiveOne($imageData);
                        Log::info('After womenPayintaneFiveOne: score=' . $score);
                        break;
                }
            }
        }

        $score = min($score, 100);
        Log::info('Final Score: ' . $score);
        Log::info('--- Calculate Score End ---');

        return $score;
    }


    public function menBalataneZero(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? '';
        Log::info("menBalataneZero - Collar before scoring: " . $collar);
        switch ($collar) {
            case 'round':
            case 'classic':
            case 'turtleneck':
            case 'hoodie':
                $point += 30;
                Log::info("menBalataneZero - Collar matched high, +30, total: $point");
                break;
            case 'V_neck':
                $point += 10;
                Log::info("menBalataneZero - Collar matched V_neck, +10, total: $point");
                break;
            default:
                Log::info("menBalataneZero - Collar no points, total: $point");
                break;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        Log::info("menBalataneZero - Sleeve before scoring: " . $sleeve);
        switch ($sleeve) {
            case 'shortsleeve':
                $point += 30;
                Log::info("menBalataneZero - Sleeve shortsleeve +30, total: $point");
                break;
            case 'sleeveless':
            case 'halfsleeve':
                $point += 10;
                Log::info("menBalataneZero - Sleeve half/sleeveless +10, total: $point");
                break;
            default:
                Log::info("menBalataneZero - Sleeve no points, total: $point");
                break;
        }

        $pattern = $data['pattern'] ?? null;
        Log::info("menBalataneZero - Pattern before scoring: " . $pattern);
        switch ($pattern) {
            case 'dorosht':
            case 'rahrahofoghi':
                $point += 20;
                Log::info("menBalataneZero - Pattern dorosht/rahrahofoghi +20, total: $point");
                break;
            case 'sade':
                $point += 10;
                Log::info("menBalataneZero - Pattern sade +10, total: $point");
                break;
            case 'riz':
            case 'rahrahamudi':
                Log::info("menBalataneZero - Pattern no points, total: $point");
                break;
            default:
                Log::info("menBalataneZero - Pattern unknown, total: $point");
                break;
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("menBalataneZero - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('bright', $tones) || in_array('light', $tones)) {
            $point += 10;
            Log::info("menBalataneZero - Color bright/light +10, total: $point");
        } else {
            Log::info("menBalataneZero - Color muted/dark +0, total: $point");
        }

        Log::info("menBalataneZero - Final score: $point");
        return $point;
    }

    public function menPayintaneZero(array $data): int
    {
        $point = 0;

        $type = $data['type'] ?? $data['shalvar'] ?? '';
        Log::info("menPayintaneZero - Type before scoring: " . $type);
        switch ($type) {
            case 'mstraight':
            case 'mslimfit':
                $point += 40;
                Log::info("menPayintaneZero - Type mstraight/mslimfit +40, total: $point");
                break;
            case 'mshorts':
            case 'mmom':
                $point += 20;
                Log::info("menPayintaneZero - Type mshorts/mmom +20, total: $point");
                break;
            case 'mbaggy':
            case 'mcargo':
            case 'mcargoshorts':
                Log::info("menPayintaneZero - Type mbaggy/mcargo/mcargoshorts +0, total: $point");
                break;
            default:
                Log::info("menPayintaneZero - Type no points, total: $point");
                break;
        }

        $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
        Log::info("menPayintaneZero - Pattern before scoring: " . $pattern);
        switch ($pattern) {
            case 'mpamudi':
            case 'mpriz':
            case 'mpsade':
                $point += 40;
                Log::info("menPayintaneZero - Pattern mpamudi/mpriz/mpsade +40, total: $point");
                break;
            case 'mpofoghi':
            case 'mpdorosht':
                Log::info("menPayintaneZero - Pattern mpofoghi/mpdorosht +0, total: $point");
                break;
            default:
                Log::info("menPayintaneZero - Pattern no points, total: $point");
                break;
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("menPayintaneZero - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('muted', $tones) || in_array('dark', $tones)) {
            $point += 10;
            Log::info("menPayintaneZero - Color muted/dark +10, total: $point");
        } else {
            Log::info("menPayintaneZero - Color bright/light +0, total: $point");
        }

        Log::info("menPayintaneZero - Final score: $point");
        return $point;
    }

    public function menBalataneTwo(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        Log::info("menBalataneTwo - Collar before scoring: " . $collar);
        switch ($collar) {
            case 'V_neck':
            case 'classic':
            case 'turtleneck':
                $point += 30;
                Log::info("menBalataneTwo - Collar V_neck/classic/turtleneck +30, total: $point");
                break;
            case 'round':
            case 'hoodie':
                $point += 10;
                Log::info("menBalataneTwo - Collar round/hoodie +10, total: $point");
                break;
            default:
                Log::info("menBalataneTwo - Collar no points, total: $point");
                break;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        Log::info("menBalataneTwo - Sleeve before scoring: " . $sleeve);
        switch ($sleeve) {
            case 'halfsleeve':
            case 'longsleeve':
            case 'sleeveless':
                $point += 30;
                Log::info("menBalataneTwo - Sleeve halfsleeve/longsleeve/sleeveless +30, total: $point");
                break;
            case 'shortsleeve':
                $point += 10;
                Log::info("menBalataneTwo - Sleeve shortsleeve +10, total: $point");
                break;
            default:
                Log::info("menBalataneTwo - Sleeve no points, total: $point");
                break;
        }

        $pattern = $data['pattern'] ?? null;
        Log::info("menBalataneTwo - Pattern before scoring: " . $pattern);
        switch ($pattern) {
            case 'rahrahamudi':
            case 'riz':
            case 'sade':
                $point += 20;
                Log::info("menBalataneTwo - Pattern rahrahamudi/riz/sade +20, total: $point");
                break;
            case 'rahrahofoghi':
            case 'dorosht':
                Log::info("menBalataneTwo - Pattern rahrahofoghi/dorosht +0, total: $point");
                break;
            default:
                Log::info("menBalataneTwo - Pattern no points, total: $point");
                break;
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("menBalataneTwo - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('muted', $tones) || in_array('dark', $tones)) {
            $point += 10;
            Log::info("menBalataneTwo - Color muted/dark +10, total: $point");
        } else {
            Log::info("menBalataneTwo - Color bright/light +0, total: $point");
        }

        Log::info("menBalataneTwo - Final score: $point");
        return $point;
    }

    public function menPayintaneTwo(array $data): int
    {
        $point = 0;

        $type = $data['type'] ?? $data['shalvar'] ?? '';
        Log::info("menPayintaneTwo - Type before scoring: " . $type);
        switch ($type) {
            case 'mbaggy':
            case 'mcargo':
                $point += 40;
                Log::info("menPayintaneTwo - Type mbaggy/mcargo +40, total: $point");
                break;
            case 'mstraight':
            case 'mmom':
            case 'mcargoshorts':
                $point += 20;
                Log::info("menPayintaneTwo - Type mstraight/mmom/mcargoshorts +20, total: $point");
                break;
            case 'mslimfit':
            case 'mshorts':
                Log::info("menPayintaneTwo - Type mslimfit/mshorts +0, total: $point");
                break;
            default:
                Log::info("menPayintaneTwo - Type no points, total: $point");
                break;
        }

        $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
        Log::info("menPayintaneTwo - Pattern before scoring: " . $pattern);
        switch ($pattern) {
            case 'mpofoghi':
            case 'mpdorosht':
                $point += 40;
                Log::info("menPayintaneTwo - Pattern mpofoghi/mpdorosht +40, total: $point");
                break;
            case 'mpamudi':
                $point += 20;
                Log::info("menPayintaneTwo - Pattern mpamudi +20, total: $point");
                break;
            case 'mpriz':
            case 'mpsade':
                Log::info("menPayintaneTwo - Pattern mpriz/mpsade +0, total: $point");
                break;
            default:
                Log::info("menPayintaneTwo - Pattern no points, total: $point");
                break;
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("menPayintaneTwo - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('bright', $tones) || in_array('light', $tones)) {
            $point += 10;
            Log::info("menPayintaneTwo - Color bright/light +10, total: $point");
        } else {
            Log::info("menPayintaneTwo - Color muted/dark +0, total: $point");
        }

        Log::info("menPayintaneTwo - Final score: $point");
        return $point;
    }

    public function menBalataneFive(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        Log::info("menBalataneFive - Collar before scoring: " . $collar);
        switch ($collar) {
            case 'round':
            case 'classic':
                $point += 30;
                Log::info("menBalataneFive - Collar round/classic +30, total: $point");
                break;
            case 'V_neck':
            case 'turtleneck':
            case 'hoodie':
                $point += 10;
                Log::info("menBalataneFive - Collar V_neck/turtleneck/hoodie +10, total: $point");
                break;
            default:
                Log::info("menBalataneFive - Collar no points, total: $point");
                break;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        Log::info("menBalataneFive - Sleeve before scoring: " . $sleeve);
        switch ($sleeve) {
            case 'shortsleeve':
            case 'longsleeve':
                $point += 30;
                Log::info("menBalataneFive - Sleeve shortsleeve/longsleeve +30, total: $point");
                break;
            case 'sleeveless':
            case 'halfsleeve':
                Log::info("menBalataneFive - Sleeve sleeveless/halfsleeve +0, total: $point");
                break;
            default:
                Log::info("menBalataneFive - Sleeve no points, total: $point");
                break;
        }

        $pattern = $data['pattern'] ?? null;
        Log::info("menBalataneFive - Pattern before scoring: " . $pattern);
        switch ($pattern) {
            case 'rahrahamudi':
            case 'riz':
            case 'sade':
                $point += 20;
                Log::info("menBalataneFive - Pattern rahrahamudi/riz/sade +20, total: $point");
                break;
            case 'dorosht':
            case 'rahrahofoghi':
                Log::info("menBalataneFive - Pattern dorosht/rahrahofoghi +0, total: $point");
                break;
            default:
                Log::info("menBalataneFive - Pattern no points, total: $point");
                break;
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("menBalataneFive - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('muted', $tones) || in_array('dark', $tones)) {
            $point += 10;
            Log::info("menBalataneFive - Color muted/dark +10, total: $point");
        } else {
            Log::info("menBalataneFive - Color bright/light +0, total: $point");
        }

        Log::info("menBalataneFive - Final score: $point");
        return $point;
    }

    public function menPayintaneFive(array $data): int
    {
        $point = 0;

        $type = $data['type'] ?? $data['shalvar'] ?? '';
        Log::info("menPayintaneFive - Type before scoring: " . $type);
        switch ($type) {
            case 'mstraight':
            case 'mbaggy':
                $point += 40;
                Log::info("menPayintaneFive - Type mstraight/mbaggy +40, total: $point");
                break;
            case 'mshorts':
            case 'mmom':
            case 'mcargo':
            case 'mcargoshorts':
                $point += 20;
                Log::info("menPayintaneFive - Type mshorts/mmom/mcargo/mcargoshorts +20, total: $point");
                break;
            case 'mslimfit':
                Log::info("menPayintaneFive - Type mslimfit +0, total: $point");
                break;
            default:
                Log::info("menPayintaneFive - Type no points, total: $point");
                break;
        }

        $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
        Log::info("menPayintaneFive - Pattern before scoring: " . $pattern);
        switch ($pattern) {
            case 'mpamudi':
            case 'mpdorosht':
            case 'mpsade':
                $point += 40;
                Log::info("menPayintaneFive - Pattern mpamudi/mpdorosht/mpsade +40, total: $point");
                break;
            case 'mpriz':
            case 'mpofoghi':
                Log::info("menPayintaneFive - Pattern mpriz/mpofoghi +0, total: $point");
                break;
            default:
                Log::info("menPayintaneFive - Pattern no points, total: $point");
                break;
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("menPayintaneFive - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('bright', $tones) || in_array('light', $tones)) {
            $point += 10;
            Log::info("menPayintaneFive - Color bright/light +10, total: $point");
        } else {
            Log::info("menPayintaneFive - Color muted/dark +0, total: $point");
        }

        Log::info("menPayintaneFive - Final score: $point");
        return $point;
    }

    public function womenBalataneOneOne(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        Log::info("womenBalataneOneOne - Collar before scoring: " . $collar);
        switch ($collar) {
            case 'off_the_shoulder':
            case 'V_neck':
            case 'squer':
            case 'sweatheart':
                $point += 20;
                Log::info("womenBalataneOneOne - Collar off_the_shoulder/V_neck/squer/sweatheart +20, total: $point");
                break;
            case 'turtleneck':
            case 'round':
            case 'one_shoulder':
            case 'halter':
            case 'boatneck':
            case 'hoodie':
            case 'classic':
                $point += 10;
                Log::info("womenBalataneOneOne - Collar turtleneck/round/one_shoulder/halter/boatneck/hoodie/classic +10, total: $point");
                break;
            default:
                Log::info("womenBalataneOneOne - Collar no points, total: $point");
                break;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        Log::info("womenBalataneOneOne - Sleeve before scoring: " . $sleeve);
        switch ($sleeve) {
            case 'fsleeveless':
            case 'fhalfsleeve':
            case 'bottompuffy':
                $point += 20;
                Log::info("womenBalataneOneOne - Sleeve fsleeveless/fhalfsleeve/bottompuffy +20, total: $point");
                break;
            case 'fshortsleeve':
            case 'flongsleeve':
                $point += 10;
                Log::info("womenBalataneOneOne - Sleeve fshortsleeve/flongsleeve +10, total: $point");
                break;
            case 'toppuffy':
                Log::info("womenBalataneOneOne - Sleeve toppuffy +0, total: $point");
                break;
            default:
                Log::info("womenBalataneOneOne - Sleeve no points, total: $point");
                break;
        }

        $loose = $data['loose'] ?? null;
        Log::info("womenBalataneOneOne - Loose before scoring: " . $loose);
        if (isset($data["loose"]) && $data["loose"] == "snatched") {
            $point += 10;
            Log::info("womenBalataneOneOne - Loose snatched +10, total: $point");
        } else {
            Log::info("womenBalataneOneOne - Loose no points, total: $point");
        }

        $wrap = $data['wrap'] ?? null;
        Log::info("womenBalataneOneOne - Wrap before scoring: " . $wrap);
        if (isset($data["wrap"]) && $data["wrap"] == "wrap") {
            $point += 5;
            Log::info("womenBalataneOneOne - Wrap wrap +5, total: $point");
        } else {
            Log::info("womenBalataneOneOne - Wrap no points, total: $point");
        }

        $peplum = $data['peplum'] ?? null;
        Log::info("womenBalataneOneOne - Peplum before scoring: " . $peplum);
        if (isset($data["peplum"]) && $data["peplum"] == "peplum") {
            $point += 5;
            Log::info("womenBalataneOneOne - Peplum peplum +5, total: $point");
        } else {
            Log::info("womenBalataneOneOne - Peplum no points, total: $point");
        }

        $belted = $data['belted'] ?? null;
        Log::info("womenBalataneOneOne - Belted before scoring: " . $belted);
        if (isset($data["belted"]) && $data["belted"] == "belted") {
            $point += 5;
            Log::info("womenBalataneOneOne - Belted belted +5, total: $point");
        } else {
            Log::info("womenBalataneOneOne - Belted no points, total: $point");
        }

        $cowl = $data['cowl'] ?? null;
        Log::info("womenBalataneOneOne - Cowl before scoring: " . $cowl);
        if (isset($data["cowl"]) && $data["cowl"] == "cowl") {
            $point += 3;
            Log::info("womenBalataneOneOne - Cowl cowl +3, total: $point");
        } else {
            Log::info("womenBalataneOneOne - Cowl no points, total: $point");
        }

        $empire = $data['empire'] ?? null;
        Log::info("womenBalataneOneOne - Empire before scoring: " . $empire);
        if (isset($data["empire"]) && $data["empire"] == "empire") {
            $point += 3;
            Log::info("womenBalataneOneOne - Empire empire +3, total: $point");
        } else {
            Log::info("womenBalataneOneOne - Empire no points, total: $point");
        }

        $pattern = $data['pattern'] ?? null;
        Log::info("womenBalataneOneOne - Pattern before scoring: " . $pattern);
        switch ($pattern) {
            case 'dorosht':
            case 'riz':
            case 'amudi':
            case 'ofoghi':
            case 'sade':
                $point += 10;
                Log::info("womenBalataneOneOne - Pattern dorosht/riz/amudi/ofoghi/sade +10, total: $point");
                break;
            default:
                Log::info("womenBalataneOneOne - Pattern no points, total: $point");
                break;
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("womenBalataneOneOne - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('muted', $tones) || in_array('dark', $tones)) {
            $point += 10;
            Log::info("womenBalataneOneOne - Color muted/dark +10, total: $point");
        }
        if (in_array('bright', $tones) || in_array('light', $tones)) {
            $point += 10;
            Log::info("womenBalataneOneOne - Color bright/light +10, total: $point");
        }
        if (!in_array('muted', $tones) && !in_array('dark', $tones) && !in_array('bright', $tones) && !in_array('light', $tones)) {
            Log::info("womenBalataneOneOne - Color no points, total: $point");
        }

        Log::info("womenBalataneOneOne - Final score: $point");
        return $point;
    }

    public function womenPayintaneOneOne(array $data): int
    {
        $point = 0;

        $skirt_and_pants = $data['skirt_and_pants'] ?? null;
        Log::info("womenPayintaneOneOne - Skirt/Pants before scoring: " . $skirt_and_pants);

        if ($skirt_and_pants === 'skirt') {
            $type = $data['skirt_type'] ?? '';
            Log::info("womenPayintaneOneOne - Skirt type before scoring: " . $type);
            switch ($type) {
                case 'pencilskirt':
                case 'wrapskirt':
                case 'mermaidskirt':
                    $point += 40;
                    Log::info("womenPayintaneOneOne - Skirt type pencilskirt/wrapskirt/mermaidskirt +40, total: $point");
                    break;
                case 'alineskirt':
                case 'miniskirt':
                case 'shortaskirt':
                    $point += 20;
                    Log::info("womenPayintaneOneOne - Skirt type alineskirt/miniskirt/shortaskirt +20, total: $point");
                    break;
                case 'balloonskirt':
                    Log::info("womenPayintaneOneOne - Skirt type balloonskirt +0, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneOneOne - Skirt type no points, total: $point");
                    break;
            }

            $pattern = $data['pattern'] ?? $data["skirt_print"] ?? null;
            Log::info("womenPayintaneOneOne - Pattern before scoring: " . $pattern);
            switch ($pattern) {
                case 'skirtsade':
                case 'skirtriz':
                case 'skirtamudi':
                case 'skirtofoghi':
                case 'skirtdorosht':
                    $point += 40;
                    Log::info("womenPayintaneOneOne - Pattern skirtsade/skirtriz/skirtamudi/skirtofoghi/skirtdorosht +40, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneOneOne - Pattern no points, total: $point");
                    break;
            }
        } elseif ($skirt_and_pants === 'pants') {
            $rise = $data['rise'] ?? null;
            Log::info("womenPayintaneOneOne - Rise before scoring: " . $rise);
            switch ($rise) {
                case 'highrise':
                    $point += 30;
                    Log::info("womenPayintaneOneOne - Rise highrise +30, total: $point");
                    break;
                case 'lowrise':
                    $point += 10;
                    Log::info("womenPayintaneOneOne - Rise lowrise +10, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneOneOne - Rise no points, total: $point");
                    break;
            }

            $type = $data['type'] ?? $data['shalvar'] ?? '';
            Log::info("womenPayintaneOneOne - Type before scoring: " . $type);
            switch ($type) {
                case 'wskinny':
                case 'wbootcut':
                case 'wbaggy':
                case 'wstraight':
                    $point += 30;
                    Log::info("womenPayintaneOneOne - Type wskinny/wbootcut/wbaggy/wstraight +30, total: $point");
                    break;
                case 'wcargo':
                case 'wshorts':
                case 'wcargoshorts':
                case 'wmom':
                    $point += 10;
                    Log::info("womenPayintaneOneOne - Type wcargo/wshorts/wcargoshorts/wmom +10, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneOneOne - Type no points, total: $point");
                    break;
            }

            $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
            Log::info("womenPayintaneOneOne - Pattern before scoring: " . $pattern);
            switch ($pattern) {
                case 'wpamudi':
                case 'wpofoghi':
                case 'wpriz':
                case 'wpdorosht':
                case 'wpsade':
                    $point += 20;
                    Log::info("womenPayintaneOneOne - Pattern wpamudi/wpofoghi/wpriz/wpdorosht/wpsade +20, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneOneOne - Pattern no points, total: $point");
                    break;
            }
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("womenPayintaneOneOne - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('muted', $tones) || in_array('dark', $tones)) {
            $point += 10;
            Log::info("womenPayintaneOneOne - Color muted/dark +10, total: $point");
        }
        if (in_array('bright', $tones) || in_array('light', $tones)) {
            $point += 10;
            Log::info("womenPayintaneOneOne - Color bright/light +10, total: $point");
        }
        if (!in_array('muted', $tones) && !in_array('dark', $tones) && !in_array('bright', $tones) && !in_array('light', $tones)) {
            Log::info("womenPayintaneOneOne - Color no points, total: $point");
        }

        Log::info("womenPayintaneOneOne - Final score: $point");
        return $point;
    }

    public function womenBalataneTwoOne(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        Log::info("womenBalataneTwoOne - Collar before scoring: " . $collar);
        switch ($collar) {
            case 'off_the_shoulder':
            case 'V_neck':
            case 'halter':
            case 'sweatheart':
            case 'one_shoulder':
                $point += 20;
                Log::info("womenBalataneTwoOne - Collar off_the_shoulder/V_neck/halter/sweatheart/one_shoulder +20, total: $point");
                break;
            case 'turtleneck':
            case 'round':
            case 'hoodie':
            case 'classic':
                $point += 10;
                Log::info("womenBalataneTwoOne - Collar turtleneck/round/hoodie/classic +10, total: $point");
                break;
            case 'squer':
            case 'boatneck':
                Log::info("womenBalataneTwoOne - Collar squer/boatneck +0, total: $point");
                break;
            default:
                Log::info("womenBalataneTwoOne - Collar no points, total: $point");
                break;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        Log::info("womenBalataneTwoOne - Sleeve before scoring: " . $sleeve);
        switch ($sleeve) {
            case 'toppuffy':
            case 'bottompuffy':
            case 'fshortsleeve':
                $point += 20;
                Log::info("womenBalataneTwoOne - Sleeve toppuffy/bottompuffy/fshortsleeve +20, total: $point");
                break;
            case 'flongsleeve':
                $point += 10;
                Log::info("womenBalataneTwoOne - Sleeve flongsleeve +10, total: $point");
                break;
            case 'fsleeveless':
            case 'fhalfsleeve':
                Log::info("womenBalataneTwoOne - Sleeve fsleeveless/fhalfsleeve +0, total: $point");
                break;
            default:
                Log::info("womenBalataneTwoOne - Sleeve no points, total: $point");
                break;
        }

        $loose = $data['loose'] ?? null;
        Log::info("womenBalataneTwoOne - Loose before scoring: " . $loose);
        if (isset($data["loose"]) && $data["loose"] == "snatched") {
            $point += 10;
            Log::info("womenBalataneTwoOne - Loose snatched +10, total: $point");
        } else {
            Log::info("womenBalataneTwoOne - Loose no points, total: $point");
        }

        $wrap = $data['wrap'] ?? null;
        Log::info("womenBalataneTwoOne - Wrap before scoring: " . $wrap);
        if (isset($data["wrap"]) && $data["wrap"] == "wrap") {
            $point += 5;
            Log::info("womenBalataneTwoOne - Wrap wrap +5, total: $point");
        } else {
            Log::info("womenBalataneTwoOne - Wrap no points, total: $point");
        }

        $peplum = $data['peplum'] ?? null;
        Log::info("womenBalataneTwoOne - Peplum before scoring: " . $peplum);
        if (isset($data["peplum"]) && $data["peplum"] == "peplum") {
            $point += 5;
            Log::info("womenBalataneTwoOne - Peplum peplum +5, total: $point");
        } else {
            Log::info("womenBalataneTwoOne - Peplum no points, total: $point");
        }

        $belted = $data['belted'] ?? null;
        Log::info("womenBalataneTwoOne - Belted before scoring: " . $belted);
        if (isset($data["belted"]) && $data["belted"] == "belted") {
            $point += 5;
            Log::info("womenBalataneTwoOne - Belted belted +5, total: $point");
        } else {
            Log::info("womenBalataneTwoOne - Belted no points, total: $point");
        }

        $cowl = $data['cowl'] ?? null;
        Log::info("womenBalataneTwoOne - Cowl before scoring: " . $cowl);
        if (isset($data["cowl"]) && $data["cowl"] == "cowl") {
            $point += 5;
            Log::info("womenBalataneTwoOne - Cowl cowl +5, total: $point");
        } else {
            Log::info("womenBalataneTwoOne - Cowl no points, total: $point");
        }

        $empire = $data['empire'] ?? null;
        Log::info("womenBalataneTwoOne - Empire before scoring: " . $empire);
        if (isset($data["empire"]) && $data["empire"] == "empire") {
            $point += 3;
            Log::info("womenBalataneTwoOne - Empire empire +3, total: $point");
        } else {
            Log::info("womenBalataneTwoOne - Empire no points, total: $point");
        }

        $pattern = $data['pattern'] ?? null;
        Log::info("womenBalataneTwoOne - Pattern before scoring: " . $pattern);
        switch ($pattern) {
            case 'ofoghi':
            case 'dorosht':
            case 'riz':
                $point += 10;
                Log::info("womenBalataneTwoOne - Pattern ofoghi/dorosht/riz +10, total: $point");
                break;
            case 'sade':
                $point += 5;
                Log::info("womenBalataneTwoOne - Pattern sade +5, total: $point");
                break;
            case 'amudi':
                Log::info("womenBalataneTwoOne - Pattern amudi +0, total: $point");
                break;
            default:
                Log::info("womenBalataneTwoOne - Pattern no points, total: $point");
                break;
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("womenBalataneTwoOne - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('muted', $tones) || in_array('dark', $tones)) {
            $point += 10;
            Log::info("womenBalataneTwoOne - Color muted/dark +10, total: $point");
        }
        if (in_array('bright', $tones) || in_array('light', $tones)) {
            $point += 10;
            Log::info("womenBalataneTwoOne - Color bright/light +10, total: $point");
        }
        if (!in_array('muted', $tones) && !in_array('dark', $tones) && !in_array('bright', $tones) && !in_array('light', $tones)) {
            Log::info("womenBalataneTwoOne - Color no points, total: $point");
        }

        Log::info("womenBalataneTwoOne - Final score: $point");
        return $point;
    }

    public function womenPayintaneTwoOne(array $data): int
    {
        $point = 0;

        $skirt_and_pants = $data['skirt_and_pants'] ?? null;
        Log::info("womenPayintaneTwoOne - Skirt/Pants before scoring: " . $skirt_and_pants);

        if ($skirt_and_pants === 'skirt') {
            $type = $data['skirt_type'] ?? '';
            Log::info("womenPayintaneTwoOne - Skirt type before scoring: " . $type);
            switch ($type) {
                case 'wrapskirt':
                case 'balloonskirt':
                case 'alineskirt':
                    $point += 40;
                    Log::info("womenPayintaneTwoOne - Skirt type wrapskirt/balloonskirt/alineskirt +40, total: $point");
                    break;
                case 'shortaskirt':
                    $point += 20;
                    Log::info("womenPayintaneTwoOne - Skirt type shortaskirt +20, total: $point");
                    break;
                case 'mermaidskirt':
                case 'miniskirt':
                case 'pencilskirt':
                    Log::info("womenPayintaneTwoOne - Skirt type mermaidskirt/miniskirt/pencilskirt +0, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneTwoOne - Skirt type no points, total: $point");
                    break;
            }

            $pattern = $data['pattern'] ?? $data["skirt_print"] ?? null;
            Log::info("womenPayintaneTwoOne - Pattern before scoring: " . $pattern);
            switch ($pattern) {
                case 'skirtofoghi':
                case 'skirtriz':
                case 'skirtdorosht':
                    $point += 40;
                    Log::info("womenPayintaneTwoOne - Pattern skirtofoghi/skirtriz/skirtdorosht +40, total: $point");
                    break;
                case 'skirtsade':
                    $point += 20;
                    Log::info("womenPayintaneTwoOne - Pattern skirtsade +20, total: $point");
                    break;
                case 'skirtamudi':
                    Log::info("womenPayintaneTwoOne - Pattern skirtamudi +0, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneTwoOne - Pattern no points, total: $point");
                    break;
            }
        } elseif ($skirt_and_pants === 'pants') {
            $rise = $data['rise'] ?? null;
            Log::info("womenPayintaneTwoOne - Rise before scoring: " . $rise);
            switch ($rise) {
                case 'highrise':
                    $point += 30;
                    Log::info("womenPayintaneTwoOne - Rise highrise +30, total: $point");
                    break;
                case 'lowrise':
                    $point += 10;
                    Log::info("womenPayintaneTwoOne - Rise lowrise +10, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneTwoOne - Rise no points, total: $point");
                    break;
            }

            $type = $data['type'] ?? $data['shalvar'] ?? '';
            Log::info("womenPayintaneTwoOne - Type before scoring: " . $type);
            switch ($type) {
                case 'wbootcut':
                case 'wmom':
                case 'wcargo':
                case 'wcargoshorts':
                    $point += 30;
                    Log::info("womenPayintaneTwoOne - Type wbootcut/wmom/wcargo/wcargoshorts +30, total: $point");
                    break;
                case 'wbaggy':
                case 'wstraight':
                case 'wshorts':
                    $point += 10;
                    Log::info("womenPayintaneTwoOne - Type wbaggy/wstraight/wshorts +10, total: $point");
                    break;
                case 'wskinny':
                    Log::info("womenPayintaneTwoOne - Type wskinny +0, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneTwoOne - Type no points, total: $point");
                    break;
            }

            $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
            Log::info("womenPayintaneTwoOne - Pattern before scoring: " . $pattern);
            switch ($pattern) {
                case 'wpofoghi':
                case 'wpriz':
                case 'wpdorosht':
                    $point += 20;
                    Log::info("womenPayintaneTwoOne - Pattern wpofoghi/wpriz/wpdorosht +20, total: $point");
                    break;
                case 'wpsade':
                    $point += 10;
                    Log::info("womenPayintaneTwoOne - Pattern wpsade +10, total: $point");
                    break;
                case 'wpamudi':
                    Log::info("womenPayintaneTwoOne - Pattern wpamudi +0, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneTwoOne - Pattern no points, total: $point");
                    break;
            }
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("womenPayintaneTwoOne - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('muted', $tones) || in_array('dark', $tones)) {
            $point += 10;
            Log::info("womenPayintaneTwoOne - Color muted/dark +10, total: $point");
        }
        if (in_array('bright', $tones) || in_array('light', $tones)) {
            $point += 10;
            Log::info("womenPayintaneTwoOne - Color bright/light +10, total: $point");
        }
        if (!in_array('muted', $tones) && !in_array('dark', $tones) && !in_array('bright', $tones) && !in_array('light', $tones)) {
            Log::info("womenPayintaneTwoOne - Color no points, total: $point");
        }

        Log::info("womenPayintaneTwoOne - Final score: $point");
        return $point;
    }

    public function womenBalataneThreeOne(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        Log::info("womenBalataneThreeOne - Collar before scoring: " . $collar);
        switch ($collar) {
            case 'one_shoulder':
            case 'V_neck':
            case 'halter':
            case 'round':
                $point += 20;
                Log::info("womenBalataneThreeOne - Collar one_shoulder/V_neck/halter/round +20, total: $point");
                break;
            case 'turtleneck':
            case 'sweatheart':
            case 'boatneck':
            case 'hoodie':
            case 'classic':
                $point += 10;
                Log::info("womenBalataneThreeOne - Collar turtleneck/sweatheart/boatneck/hoodie/classic +10, total: $point");
                break;
            case 'squer':
            case 'off_the_shoulder':
                Log::info("womenBalataneThreeOne - Collar squer/off_the_shoulder +0, total: $point");
                break;
            default:
                Log::info("womenBalataneThreeOne - Collar no points, total: $point");
                break;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        Log::info("womenBalataneThreeOne - Sleeve before scoring: " . $sleeve);
        switch ($sleeve) {
            case 'fshortsleeve':
            case 'flongsleeve':
            case 'bottompuffy':
                $point += 20;
                Log::info("womenBalataneThreeOne - Sleeve fshortsleeve/flongsleeve/bottompuffy +20, total: $point");
                break;
            case 'fhalfsleeve':
            case 'fsleeveless':
                $point += 10;
                Log::info("womenBalataneThreeOne - Sleeve fhalfsleeve/fsleeveless +10, total: $point");
                break;
            case 'toppuffy':
                Log::info("womenBalataneThreeOne - Sleeve toppuffy +0, total: $point");
                break;
            default:
                Log::info("womenBalataneThreeOne - Sleeve no points, total: $point");
                break;
        }

        $loose = $data['loose'] ?? null;
        Log::info("womenBalataneThreeOne - Loose before scoring: " . $loose);
        if (isset($data["loose"]) && $data["loose"] == "snatched") {
            $point += 10;
            Log::info("womenBalataneThreeOne - Loose snatched +10, total: $point");
        } else {
            Log::info("womenBalataneThreeOne - Loose no points, total: $point");
        }

        $wrap = $data['wrap'] ?? null;
        Log::info("womenBalataneThreeOne - Wrap before scoring: " . $wrap);
        if (isset($data["wrap"]) && $data["wrap"] == "wrap") {
            $point += 5;
            Log::info("womenBalataneThreeOne - Wrap wrap +5, total: $point");
        } else {
            Log::info("womenBalataneThreeOne - Wrap no points, total: $point");
        }

        $peplum = $data['peplum'] ?? null;
        Log::info("womenBalataneThreeOne - Peplum before scoring: " . $peplum);
        if (isset($data["peplum"]) && $data["peplum"] == "peplum") {
            $point += 5;
            Log::info("womenBalataneThreeOne - Peplum peplum +5, total: $point");
        } else {
            Log::info("womenBalataneThreeOne - Peplum no points, total: $point");
        }

        $belted = $data['belted'] ?? null;
        Log::info("womenBalataneThreeOne - Belted before scoring: " . $belted);
        if (isset($data["belted"]) && $data["belted"] == "belted") {
            $point += 5;
            Log::info("womenBalataneThreeOne - Belted belted +5, total: $point");
        } else {
            Log::info("womenBalataneThreeOne - Belted no points, total: $point");
        }

        $empire = $data['empire'] ?? null;
        Log::info("womenBalataneThreeOne - Empire before scoring: " . $empire);
        if (isset($data["empire"]) && $data["empire"] == "empire") {
            $point += 3;
            Log::info("womenBalataneThreeOne - Empire empire +3, total: $point");
        } else {
            Log::info("womenBalataneThreeOne - Empire no points, total: $point");
        }

        $pattern = $data['pattern'] ?? null;
        Log::info("womenBalataneThreeOne - Pattern before scoring: " . $pattern);
        switch ($pattern) {
            case 'amudi':
            case 'riz':
            case 'sade':
                $point += 10;
                Log::info("womenBalataneThreeOne - Pattern amudi/riz/sade +10, total: $point");
                break;
            case 'ofoghi':
            case 'dorosht':
                Log::info("womenBalataneThreeOne - Pattern ofoghi/dorosht +0, total: $point");
                break;
            default:
                Log::info("womenBalataneThreeOne - Pattern no points, total: $point");
                break;
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("womenBalataneThreeOne - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('muted', $tones) || in_array('dark', $tones)) {
            $point += 10;
            Log::info("womenBalataneThreeOne - Color muted/dark +10, total: $point");
        } else {
            Log::info("womenBalataneThreeOne - Color bright/light +0, total: $point");
        }

        Log::info("womenBalataneThreeOne - Final score: $point");
        return $point;
    }

    public function womenPayintaneThreeOne(array $data): int
    {
        $point = 0;

        $skirt_and_pants = $data['skirt_and_pants'] ?? null;
        Log::info("womenPayintaneThreeOne - Skirt/Pants before scoring: " . $skirt_and_pants);

        if ($skirt_and_pants === 'skirt') {
            $type = $data['skirt_type'] ?? null;
            Log::info("womenPayintaneThreeOne - Skirt type before scoring: " . $type);
            if (in_array($type, ['balloonskirt', 'alineskirt', 'shortaskirt', 'wrapskirt'])) {
                $point += 40;
                Log::info("womenPayintaneThreeOne - Skirt type balloonskirt/alineskirt/shortaskirt/wrapskirt +40, total: $point");
            } elseif ($type === 'mermaidskirt') {
                $point += 20;
                Log::info("womenPayintaneThreeOne - Skirt type mermaidskirt +20, total: $point");
            } elseif (in_array($type, ['pencilskirt', 'miniskirt'])) {
                Log::info("womenPayintaneThreeOne - Skirt type pencilskirt/miniskirt +0, total: $point");
            } else {
                Log::info("womenPayintaneThreeOne - Skirt type no points, total: $point");
            }

            $pattern = $data['pattern'] ?? $data["skirt_print"] ?? null;
            Log::info("womenPayintaneThreeOne - Pattern before scoring: " . $pattern);
            if (in_array($pattern, ['skirtofoghi', 'skirtriz', 'skirtdorosht'])) {
                $point += 40;
                Log::info("womenPayintaneThreeOne - Pattern skirtofoghi/skirtriz/skirtdorosht +40, total: $point");
            } elseif ($pattern === 'skirtamudi') {
                $point += 20;
                Log::info("womenPayintaneThreeOne - Pattern skirtamudi +20, total: $point");
            } elseif (in_array($pattern, ['skirtriz', 'skirtsade'])) {
                Log::info("womenPayintaneThreeOne - Pattern skirtriz/skirtsade +0, total: $point");
            } else {
                Log::info("womenPayintaneThreeOne - Pattern no points, total: $point");
            }
        } elseif ($skirt_and_pants === 'pants') {
            $rise = $data['rise'] ?? null;
            Log::info("womenPayintaneThreeOne - Rise before scoring: " . $rise);
            if ($rise === 'highrise') {
                $point += 30;
                Log::info("womenPayintaneThreeOne - Rise highrise +30, total: $point");
            } elseif ($rise === 'lowrise') {
                $point += 10;
                Log::info("womenPayintaneThreeOne - Rise lowrise +10, total: $point");
            } else {
                Log::info("womenPayintaneThreeOne - Rise no points, total: $point");
            }

            $type = $data['type'] ?? $data['shalvar'] ?? null;
            Log::info("womenPayintaneThreeOne - Type before scoring: " . $type);
            if (in_array($type, ['wbaggy', 'wcargo', 'wcargoshorts', 'wbootcut', 'wmom'])) {
                $point += 30;
                Log::info("womenPayintaneThreeOne - Type wbaggy/wcargo/wcargoshorts/wbootcut/wmom +30, total: $point");
            } elseif (in_array($type, ['wstraight', 'wshorts'])) {
                $point += 10;
                Log::info("womenPayintaneThreeOne - Type wstraight/wshorts +10, total: $point");
            } elseif ($type === 'wskinny') {
                Log::info("womenPayintaneThreeOne - Type wskinny +0, total: $point");
            } else {
                Log::info("womenPayintaneThreeOne - Type no points, total: $point");
            }

            $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? null;
            Log::info("womenPayintaneThreeOne - Pattern before scoring: " . $pattern);
            if (in_array($pattern, ['wpofoghi', 'wpriz', 'wpdorosht'])) {
                $point += 20;
                Log::info("womenPayintaneThreeOne - Pattern wpofoghi/wpriz/wpdorosht +20, total: $point");
            } elseif ($pattern === 'wpamudi') {
                $point += 10;
                Log::info("womenPayintaneThreeOne - Pattern wpamudi +10, total: $point");
            } elseif (in_array($pattern, ['wpriz', 'wpsade'])) {
                Log::info("womenPayintaneThreeOne - Pattern wpriz/wpsade +0, total: $point");
            } else {
                Log::info("womenPayintaneThreeOne - Pattern no points, total: $point");
            }
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("womenPayintaneThreeOne - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('bright', $tones) || in_array('light', $tones)) {
            $point += 10;
            Log::info("womenPayintaneThreeOne - Color bright/light +10, total: $point");
        } else {
            Log::info("womenPayintaneThreeOne - Color muted/dark +0, total: $point");
        }

        Log::info("womenPayintaneThreeOne - Final score: $point");
        return $point;
    }

    public function womenBalataneFourOne(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        Log::info("womenBalataneFourOne - Collar before scoring: " . $collar);
        if (in_array($collar, ['boatneck', 'round', 'sweatheart', 'off_the_shoulder', 'classic', 'hoodie'])) {
            $point += 20;
            Log::info("womenBalataneFourOne - Collar boatneck/round/sweatheart/off_the_shoulder/classic/hoodie +20, total: $point");
        } elseif (in_array($collar, ['squer', 'turtleneck', 'one_shoulder'])) {
            $point += 10;
            Log::info("womenBalataneFourOne - Collar squer/turtleneck/one_shoulder +10, total: $point");
        } elseif (in_array($collar, ['halter', 'V_neck'])) {
            Log::info("womenBalataneFourOne - Collar halter/V_neck +0, total: $point");
        } else {
            Log::info("womenBalataneFourOne - Collar no points, total: $point");
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        Log::info("womenBalataneFourOne - Sleeve before scoring: " . $sleeve);
        if (in_array($sleeve, ['toppuffy', 'fhalfsleeve', 'fshortsleeve'])) {
            $point += 20;
            Log::info("womenBalataneFourOne - Sleeve toppuffy/fhalfsleeve/fshortsleeve +20, total: $point");
        } elseif ($sleeve === 'fsleeveless') {
            $point += 10;
            Log::info("womenBalataneFourOne - Sleeve fsleeveless +10, total: $point");
        } elseif (in_array($sleeve, ['flongsleeve', 'bottompuffy'])) {
            Log::info("womenBalataneFourOne - Sleeve flongsleeve/bottompuffy +0, total: $point");
        } else {
            Log::info("womenBalataneFourOne - Sleeve no points, total: $point");
        }

        $loose = $data['loose'] ?? null;
        Log::info("womenBalataneFourOne - Loose before scoring: " . $loose);
        if (isset($data["loose"]) && $data["loose"] == "snatched") {
            $point += 10;
            Log::info("womenBalataneFourOne - Loose snatched +10, total: $point");
        } else {
            Log::info("womenBalataneFourOne - Loose no points, total: $point");
        }

        $wrap = $data['wrap'] ?? null;
        Log::info("womenBalataneFourOne - Wrap before scoring: " . $wrap);
        if (isset($data["wrap"]) && $data["wrap"] == "wrap") {
            $point += 5;
            Log::info("womenBalataneFourOne - Wrap wrap +5, total: $point");
        } else {
            Log::info("womenBalataneFourOne - Wrap no points, total: $point");
        }

        $cowl = $data['cowl'] ?? null;
        Log::info("womenBalataneFourOne - Cowl before scoring: " . $cowl);
        if (isset($data["cowl"]) && $data["cowl"] == "cowl") {
            $point += 5;
            Log::info("womenBalataneFourOne - Cowl cowl +5, total: $point");
        } else {
            Log::info("womenBalataneFourOne - Cowl no points, total: $point");
        }

        $belted = $data['belted'] ?? null;
        Log::info("womenBalataneFourOne - Belted before scoring: " . $belted);
        if (isset($data["belted"]) && $data["belted"] == "belted") {
            $point += 5;
            Log::info("womenBalataneFourOne - Belted belted +5, total: $point");
        } else {
            Log::info("womenBalataneFourOne - Belted no points, total: $point");
        }

        $peplum = $data['peplum'] ?? null;
        Log::info("womenBalataneFourOne - Peplum before scoring: " . $peplum);
        if (isset($data["peplum"]) && $data["peplum"] == "peplum") {
            $point += 3;
            Log::info("womenBalataneFourOne - Peplum peplum +3, total: $point");
        } else {
            Log::info("womenBalataneFourOne - Peplum no points, total: $point");
        }

        $empire = $data['empire'] ?? null;
        Log::info("womenBalataneFourOne - Empire before scoring: " . $empire);
        if (isset($data["empire"]) && $data["empire"] == "empire") {
            $point += 3;
            Log::info("womenBalataneFourOne - Empire empire +3, total: $point");
        } else {
            Log::info("womenBalataneFourOne - Empire no points, total: $point");
        }

        $pattern = $data['pattern'] ?? null;
        Log::info("womenBalataneFourOne - Pattern before scoring: " . $pattern);
        if (in_array($pattern, ['ofoghi', 'dorosht'])) {
            $point += 10;
            Log::info("womenBalataneFourOne - Pattern ofoghi/dorosht +10, total: $point");
        } elseif ($pattern === 'amudi') {
            $point += 5;
            Log::info("womenBalataneFourOne - Pattern amudi +5, total: $point");
        } elseif (in_array($pattern, ['riz', 'sade'])) {
            Log::info("womenBalataneFourOne - Pattern riz/sade +0, total: $point");
        } else {
            Log::info("womenBalataneFourOne - Pattern no points, total: $point");
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("womenBalataneFourOne - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('bright', $tones) || in_array('light', $tones)) {
            $point += 10;
            Log::info("womenBalataneFourOne - Color bright/light +10, total: $point");
        } else {
            Log::info("womenBalataneFourOne - Color muted/dark +0, total: $point");
        }

        Log::info("womenBalataneFourOne - Final score: $point");
        return $point;
    }

    public function womenPayintaneFourOne(array $data): int
    {
        $point = 0;

        $skirt_and_pants = $data['skirt_and_pants'] ?? null;
        Log::info("womenPayintaneFourOne - Skirt/Pants before scoring: " . $skirt_and_pants);

        if ($skirt_and_pants === 'skirt') {
            $type = $data['skirt_type'] ?? '';
            Log::info("womenPayintaneFourOne - Skirt type before scoring: " . $type);
            switch ($type) {
                case 'alineskirt':
                case 'pencilskirt':
                case 'wrapskirt':
                    $point += 40;
                    Log::info("womenPayintaneFourOne - Skirt type alineskirt/pencilskirt/wrapskirt +40, total: $point");
                    break;
                case 'mermaidskirt':
                case 'shortaskirt':
                    $point += 20;
                    Log::info("womenPayintaneFourOne - Skirt type mermaidskirt/shortaskirt +20, total: $point");
                    break;
                case 'balloonskirt':
                case 'miniskirt':
                    Log::info("womenPayintaneFourOne - Skirt type balloonskirt/miniskirt +0, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneFourOne - Skirt type no points, total: $point");
                    break;
            }

            $pattern = $data['pattern'] ?? $data["skirt_print"] ?? null;
            Log::info("womenPayintaneFourOne - Pattern before scoring: " . $pattern);
            switch ($pattern) {
                case 'skirtamudi':
                case 'skirtriz':
                case 'skirtsade':
                    $point += 40;
                    Log::info("womenPayintaneFourOne - Pattern skirtamudi/skirtriz/skirtsade +40, total: $point");
                    break;
                case 'skirtofoghi':
                case 'skirtdorosht':
                    Log::info("womenPayintaneFourOne - Pattern skirtofoghi/skirtdorosht +0, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneFourOne - Pattern no points, total: $point");
                    break;
            }
        } elseif ($skirt_and_pants === 'pants') {
            $rise = $data['rise'] ?? null;
            Log::info("womenPayintaneFourOne - Rise before scoring: " . $rise);
            if ($rise === 'highrise') {
                $point += 30;
                Log::info("womenPayintaneFourOne - Rise highrise +30, total: $point");
            } elseif ($rise === 'lowrise') {
                Log::info("womenPayintaneFourOne - Rise lowrise +0, total: $point");
            } else {
                Log::info("womenPayintaneFourOne - Rise no points, total: $point");
            }

            $type = $data['type'] ?? $data['shalvar'] ?? null;
            Log::info("womenPayintaneFourOne - Type before scoring: " . $type);
            if (in_array($type, ['wskinny', 'wbaggy', 'wbootcut', 'wstraight'])) {
                $point += 30;
                Log::info("womenPayintaneFourOne - Type wskinny/wbaggy/wbootcut/wstraight +30, total: $point");
            } elseif ($type === 'wshorts') {
                $point += 10;
                Log::info("womenPayintaneFourOne - Type wshorts +10, total: $point");
            } elseif (in_array($type, ['wmom', 'wcargo', 'wcargoshorts'])) {
                Log::info("womenPayintaneFourOne - Type wmom/wcargo/wcargoshorts +0, total: $point");
            } else {
                Log::info("womenPayintaneFourOne - Type no points, total: $point");
            }

            $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
            Log::info("womenPayintaneFourOne - Pattern before scoring: " . $pattern);
            switch ($pattern) {
                case 'wpamudi':
                case 'wpriz':
                case 'wpsade':
                    $point += 20;
                    Log::info("womenPayintaneFourOne - Pattern wpamudi/wpriz/wpsade +20, total: $point");
                    break;
                case 'wpofoghi':
                case 'wpdorosht':
                    Log::info("womenPayintaneFourOne - Pattern wpofoghi/wpdorosht +0, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneFourOne - Pattern no points, total: $point");
                    break;
            }
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("womenPayintaneFourOne - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('muted', $tones) || in_array('dark', $tones)) {
            $point += 10;
            Log::info("womenPayintaneFourOne - Color muted/dark +10, total: $point");
        } else {
            Log::info("womenPayintaneFourOne - Color bright/light +0, total: $point");
        }

        Log::info("womenPayintaneFourOne - Final score: $point");
        return $point;
    }

    public function womenBalataneFiveOne(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        Log::info("womenBalataneFiveOne - Collar before scoring: " . $collar);
        if (in_array($collar, ['one_shoulder', 'off_the_shoulder', 'V_neck', 'squer'])) {
            $point += 20;
            Log::info("womenBalataneFiveOne - Collar one_shoulder/off_the_shoulder/V_neck/squer +20, total: $point");
        } elseif (in_array($collar, ['round', 'sweatheart', 'boatneck', 'hoodie', 'classic'])) {
            $point += 10;
            Log::info("womenBalataneFiveOne - Collar round/sweatheart/boatneck/hoodie/classic +10, total: $point");
        } elseif (in_array($collar, ['halter', 'turtleneck'])) {
            Log::info("womenBalataneFiveOne - Collar halter/turtleneck +0, total: $point");
        } else {
            Log::info("womenBalataneFiveOne - Collar no points, total: $point");
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        Log::info("womenBalataneFiveOne - Sleeve before scoring: " . $sleeve);
        if (in_array($sleeve, ['fshortsleeve', 'flongsleeve', 'fsleeveless', 'bottompuffy'])) {
            $point += 20;
            Log::info("womenBalataneFiveOne - Sleeve fshortsleeve/flongsleeve/fsleeveless/bottompuffy +20, total: $point");
        } elseif (in_array($sleeve, ['fhalfsleeve', 'toppuffy'])) {
            Log::info("womenBalataneFiveOne - Sleeve fhalfsleeve/toppuffy +0, total: $point");
        } else {
            Log::info("womenBalataneFiveOne - Sleeve no points, total: $point");
        }

        $wrap = $data['wrap'] ?? null;
        Log::info("womenBalataneFiveOne - Wrap before scoring: " . $wrap);
        if (isset($data["wrap"]) && $data["wrap"] == "wrap") {
            $point += 5;
            Log::info("womenBalataneFiveOne - Wrap wrap +5, total: $point");
        } else {
            Log::info("womenBalataneFiveOne - Wrap no points, total: $point");
        }

        $belted = $data['belted'] ?? null;
        Log::info("womenBalataneFiveOne - Belted before scoring: " . $belted);
        if (isset($data["belted"]) && $data["belted"] == "belted") {
            $point += 5;
            Log::info("womenBalataneFiveOne - Belted belted +5, total: $point");
        } else {
            Log::info("womenBalataneFiveOne - Belted no points, total: $point");
        }

        $peplum = $data['peplum'] ?? null;
        Log::info("womenBalataneFiveOne - Peplum before scoring: " . $peplum);
        if (isset($data["peplum"]) && $data["peplum"] == "peplum") {
            $point += 5;
            Log::info("womenBalataneFiveOne - Peplum peplum +5, total: $point");
        } else {
            Log::info("womenBalataneFiveOne - Peplum no points, total: $point");
        }

        $empire = $data['empire'] ?? null;
        Log::info("womenBalataneFiveOne - Empire before scoring: " . $empire);
        if (isset($data["empire"]) && $data["empire"] == "empire") {
            $point += 5;
            Log::info("womenBalataneFiveOne - Empire empire +5, total: $point");
        } else {
            Log::info("womenBalataneFiveOne - Empire no points, total: $point");
        }

        $loose = $data['loose'] ?? null;
        Log::info("womenBalataneFiveOne - Loose before scoring: " . $loose);
        if (isset($data["loose"]) && ($data["loose"] == "loose" || $data["loose"] == "losse")) {
            $point += 5;
            Log::info("womenBalataneFiveOne - Loose loose/losse +5, total: $point");
        } else {
            Log::info("womenBalataneFiveOne - Loose no points, total: $point");
        }

        $cowl = $data['cowl'] ?? null;
        Log::info("womenBalataneFiveOne - Cowl before scoring: " . $cowl);
        if (isset($data["cowl"]) && $data["cowl"] == "cowl") {
            $point += 3;
            Log::info("womenBalataneFiveOne - Cowl cowl +3, total: $point");
        } else {
            Log::info("womenBalataneFiveOne - Cowl no points, total: $point");
        }

        $pattern = $data['pattern'] ?? null;
        Log::info("womenBalataneFiveOne - Pattern before scoring: " . $pattern);
        if (in_array($pattern, ['amudi', 'riz', 'sade'])) {
            $point += 10;
            Log::info("womenBalataneFiveOne - Pattern amudi/riz/sade +10, total: $point");
        } elseif (in_array($pattern, ['dorosht', 'ofoghi'])) {
            Log::info("womenBalataneFiveOne - Pattern dorosht/ofoghi +0, total: $point");
        } else {
            Log::info("womenBalataneFiveOne - Pattern no points, total: $point");
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("womenBalataneFiveOne - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('muted', $tones) || in_array('dark', $tones)) {
            $point += 10;
            Log::info("womenBalataneFiveOne - Color muted/dark +10, total: $point");
        } else {
            Log::info("womenBalataneFiveOne - Color bright/light +0, total: $point");
        }

        Log::info("womenBalataneFiveOne - Final score: $point");
        return $point;
    }

    public function womenPayintaneFiveOne(array $data): int
    {
        $point = 0;

        $skirt_and_pants = $data['skirt_and_pants'] ?? null;
        Log::info("womenPayintaneFiveOne - Skirt/Pants before scoring: " . $skirt_and_pants);

        if ($skirt_and_pants === 'skirt') {
            $type = $data['skirt_type'] ?? '';
            Log::info("womenPayintaneFiveOne - Skirt type before scoring: " . $type);
            switch ($type) {
                case 'wrapskirt':
                case 'pencilskirt':
                case 'shortaskirt':
                    $point += 40;
                    Log::info("womenPayintaneFiveOne - Skirt type wrapskirt/pencilskirt/shortaskirt +40, total: $point");
                    break;
                case 'alineskirt':
                case 'miniskirt':
                    $point += 20;
                    Log::info("womenPayintaneFiveOne - Skirt type alineskirt/miniskirt +20, total: $point");
                    break;
                case 'mermaidskirt':
                case 'balloonskirt':
                    Log::info("womenPayintaneFiveOne - Skirt type mermaidskirt/balloonskirt +0, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneFiveOne - Skirt type no points, total: $point");
                    break;
            }

            $pattern = $data['pattern'] ?? $data["skirt_print"] ?? null;
            Log::info("womenPayintaneFiveOne - Pattern before scoring: " . $pattern);
            switch ($pattern) {
                case 'skirtofoghi':
                case 'skirtdorosht':
                    $point += 40;
                    Log::info("womenPayintaneFiveOne - Pattern skirtofoghi/skirtdorosht +40, total: $point");
                    break;
                case 'skirtsade':
                    $point += 20;
                    Log::info("womenPayintaneFiveOne - Pattern skirtsade +20, total: $point");
                    break;
                case 'skirtriz':
                case 'skirtamudi':
                    Log::info("womenPayintaneFiveOne - Pattern skirtriz/skirtamudi +0, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneFiveOne - Pattern no points, total: $point");
                    break;
            }
        } elseif ($skirt_and_pants === 'pants') {
            $rise = $data['rise'] ?? null;
            Log::info("womenPayintaneFiveOne - Rise before scoring: " . $rise);
            if ($rise === 'highrise') {
                $point += 30;
                Log::info("womenPayintaneFiveOne - Rise highrise +30, total: $point");
            } elseif ($rise === 'lowrise') {
                Log::info("womenPayintaneFiveOne - Rise lowrise +0, total: $point");
            } else {
                Log::info("womenPayintaneFiveOne - Rise no points, total: $point");
            }

            $type = $data['type'] ?? $data['shalvar'] ?? null;
            Log::info("womenPayintaneFiveOne - Type before scoring: " . $type);
            if (in_array($type, ['wbaggy', 'wstraight', 'wmom', 'wshorts'])) {
                $point += 30;
                Log::info("womenPayintaneFiveOne - Type wbaggy/wstraight/wmom/wshorts +30, total: $point");
            } elseif (in_array($type, ['wbootcut', 'wcargo', 'wcargoshorts'])) {
                $point += 10;
                Log::info("womenPayintaneFiveOne - Type wbootcut/wcargo/wcargoshorts +10, total: $point");
            } elseif ($type === 'wskinny') {
                Log::info("womenPayintaneFiveOne - Type wskinny +0, total: $point");
            } else {
                Log::info("womenPayintaneFiveOne - Type no points, total: $point");
            }

            $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
            Log::info("womenPayintaneFiveOne - Pattern before scoring: " . $pattern);
            switch ($pattern) {
                case 'wpofoghi':
                case 'wpdorosht':
                    $point += 20;
                    Log::info("womenPayintaneFiveOne - Pattern wpofoghi/wpdorosht +20, total: $point");
                    break;
                case 'wpsade':
                    $point += 10;
                    Log::info("womenPayintaneFiveOne - Pattern wpsade +10, total: $point");
                    break;
                case 'wpamudi':
                    Log::info("womenPayintaneFiveOne - Pattern wpamudi +0, total: $point");
                    break;
                default:
                    Log::info("womenPayintaneFiveOne - Pattern no points, total: $point");
                    break;
            }
        }

        $color = $data['color'] ?? $data['color_tone'] ?? '';
        Log::info("womenPayintaneFiveOne - Color before scoring: " . $color);
        $tones = explode('_', $color);
        if (in_array('bright', $tones) || in_array('light', $tones)) {
            $point += 10;
            Log::info("womenPayintaneFiveOne - Color bright/light +10, total: $point");
        } else {
            Log::info("womenPayintaneFiveOne - Color muted/dark +0, total: $point");
        }

        Log::info("womenPayintaneFiveOne - Final score: $point");
        return $point;
    }
}
