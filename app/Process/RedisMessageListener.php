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

        if ($imageData['paintane'] === 'mpayintane' || $imageData['paintane'] === 'mbalatane') {
            if ($imageData['paintane'] === 'mbalatane') {
                switch ((int)$userBodyType) {
                    case 0:
                        $score += $this->menBalataneZero($imageData);
                        break;
                    case 1:
                        $score += $this->menBalataneTwo($imageData);
                        break;
                    case 2:
                        $score += $this->menBalataneFive($imageData);
                        break;
                }

            } elseif ($imageData['paintane'] === 'mpayintane') {
                switch ((int)$userBodyType) {
                    case 0:
                        $score += $this->menPayintaneZero($imageData);
                        break;
                    case 1:
                        $score += $this->menPayintaneTwo($imageData);
                        break;
                    case 2:
                        $score += $this->menPayintaneFive($imageData);
                        break;
                }

            }
        } elseif ($imageData['paintane'] === 'fbalatane' || $imageData['paintane'] === 'fpayintane' || $imageData['paintane'] === 'ftamamtane') {
            if ($imageData['paintane'] === 'fbalatane') {
                switch ((int)$userBodyType) {
                    case 11:
                        $score += $this->womenBalataneOneOne($imageData);
                        break;
                    case 21:
                        $score += $this->womenBalataneTwoOne($imageData);
                        break;
                    case 31:
                        $score += $this->womenBalataneThreeOne($imageData);
                        break;
                    case 41:
                        $score += $this->womenBalataneFourOne($imageData);
                        break;
                    case 51:
                        $score += $this->womenBalataneFiveOne($imageData);
                        break;
                }

            } else {
                switch ((int)$userBodyType) {
                    case 11:
                        $score += $this->womenPayintaneOneOne($imageData);
                        break;
                    case 21:
                        $score += $this->womenPayintaneTwoOne($imageData);
                        break;
                    case 31:
                        $score += $this->womenPayintaneThreeOne($imageData);
                        break;
                    case 41:
                        $score += $this->womenPayintaneFourOne($imageData);
                        break;
                    case 51:
                        $score += $this->womenPayintaneFiveOne($imageData);
                        break;
                }
            }
        }

        // Ensure score does not exceed 100
        return min($score, 100);
    }


    public function menBalataneZero(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? '';
        switch ($collar) {
            case 'round':
            case 'classic':
            case 'turtleneck':
            case 'hoodie':
                $point += 30;
                break;
            case 'V_neck':
                $point += 10;
                break;
            default:
                // No points added
                break;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        switch ($sleeve) {
            case 'shortsleeve':
                $point += 30;
                break;
            case 'sleeveless':
            case 'halfsleeve':
                $point += 10;
                break;
            default:
                // No points added
                break;
        }

        switch ($data['pattern'] ?? null) {
            case 'dorosht':
            case 'rahrahofoghi':
                $point += 20;
                break;
            case 'sade':
                $point += 10;
                break;
            case 'riz':
            case 'rahrahamudi':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 0 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 10 : 0;

        return $point;
    }


    public function menPayintaneZero(array $data): int
    {
        $point = 0;

        $type = $data['type'] ?? $data['shalvar'] ?? '';
        switch ($type) {
            case 'mstraight':
            case 'mslimfit':
                $point += 40;
                break;
            case 'mshorts':
            case 'mmom':
                $point += 20;
                break;
            case 'mbaggy':
            case 'mcargo':
            case 'mcargoshorts':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
        switch ($pattern) {
            case 'mpamudi':
            case 'mpriz':
            case 'mpsade':
                $point += 40;
                break;
            case 'mpofoghi':
            case 'mpdorosht':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }


        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 10 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 0 : 0;


        return $point;
    }

    public function menBalataneTwo(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        switch ($collar) {
            case 'V_neck':
            case 'classic':
            case 'turtleneck':
                $point += 30;
                break;
            case 'round':
            case 'hoodie':
                $point += 10;
                break;
            default:
                // No points added
                break;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        switch ($sleeve) {
            case 'halfsleeve':
            case 'longsleeve':
            case 'sleeveless':
                $point += 30;
                break;
            case 'shortsleeve':
                $point += 10;
                break;
            default:
                // No points added
                break;
        }

        switch ($data['pattern'] ?? null) {
            case 'rahrahamudi':
            case 'riz':
            case 'sade':
                $point += 20;
                break;
            case 'rahrahofoghi':
            case 'dorosht':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 10 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 0 : 0;

        return $point;

    }


    public function menPayintaneTwo(array $data): int
    {
        $point = 0;

        $type = $data['type'] ?? $data['shalvar'] ?? '';
        switch ($type) {
            case 'mbaggy':
            case 'mcargo':
                $point += 40;
                break;
            case 'mstraight':
            case 'mmom':
            case 'mcargoshorts':
                $point += 20;
                break;
            case 'mslimfit':
            case 'mshorts':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
        switch ($pattern) {
            case 'mpofoghi':
            case 'mpdorosht':
                $point += 40;
                break;
            case 'mpamudi':
                $point += 20;
                break;
            case 'mpriz':
            case 'mpsade':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 0 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 10 : 0;

        return $point;
    }


    public function menBalataneFive(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        switch ($collar) {
            case 'round':
            case 'classic':
                $point += 30;
                break;
            case 'V_neck':
                $point += 10;
                break;
            case 'turtleneck':
            case 'hoodie':
                $point += 10;
                break;
            default:
                // No points added
                break;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        switch ($sleeve) {
            case 'shortsleeve':
            case 'longsleeve':
                $point += 30;
                break;
            case 'sleeveless':
            case 'halfsleeve':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        switch ($data['pattern'] ?? null) {
            case 'rahrahamudi':
            case 'riz':
            case 'sade':
                $point += 20;
                break;
            case 'dorosht':
            case 'rahrahofoghi':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 10 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 0 : 0;

        return $point;
    }


    public function menPayintaneFive(array $data): int
    {
        $point = 0;

        $type = $data['type'] ?? $data['shalvar'] ?? '';
        switch ($type) {
            case 'mstraight':
            case 'mbaggy':
                $point += 40;
                break;
            case 'mshorts':
            case 'mmom':
            case 'mcargo':
            case 'mcargoshorts':
                $point += 20;
                break;
            case 'mslimfit':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
        switch ($pattern) {
            case 'mpamudi':
            case 'mpdorosht':
            case 'mpsade':
                $point += 40;
                break;
            case 'mpriz':
            case 'mpofoghi':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 0 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 10 : 0;

        return $point;
    }


    public function womenBalataneOneOne(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        switch ($collar) {
            case 'off_the_shoulder':
            case 'V_neck':
            case 'squer':
            case 'sweatheart':
                $point += 20;
                break;
            case 'turtleneck':
            case 'round':
            case 'one_shoulder':
            case 'halter':
            case 'boatneck':
            case 'hoodie':
            case 'classic':
                $point += 10;
                break;
            default:
                // No points added
                break;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        switch ($sleeve) {
            case 'fsleeveless':
            case 'fhalfsleeve':
            case 'bottompuffy':
                $point += 20;
                break;
            case 'fshortsleeve':
            case 'flongsleeve':
                $point += 10;
                break;
            case 'toppuffy':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }


        $point += isset($data["loose"]) ? $data["loose"] == "snatched" ? 10 : 0 : 0;
        $point += isset($data["wrap"]) ? $data["wrap"] == "wrap" ? 5 : 0 : 0;
        $point += isset($data["peplum"]) ? $data["peplum"] == "peplum" ? 5 : 0 : 0;
        $point += isset($data["belted"]) ? $data["belted"] == "belted" ? 5 : 0 : 0;
        $point += isset($data["cowl"]) ? $data["cowl"] == "cowl" ? 3 : 0 : 0;
        $point += isset($data["empire"]) ? $data["empire"] == "empire" ? 3 : 0 : 0;


        switch ($data['pattern'] ?? null) {
            case 'dorosht':
            case 'riz':
            case 'amudi':
            case 'ofoghi':
            case 'sade':
                $point += 10;
                break;
            default:
                // No points added
                break;
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 10 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 10 : 0;

        return $point;
    }


    public function womenPayintaneOneOne(array $data): int
    {
        $point = 0;

        if (($data['skirt_and_pants'] ?? null) === 'skirt') {
            $type = $data['skirt_type'] ?? '';
            switch ($type) {
                case 'pencilskirt':
                case 'wrapskirt':
                case 'mermaidskirt':
                    $point += 40;
                    break;
                case 'alineskirt':
                case 'miniskirt':
                case 'shortaskirt':
                    $point += 20;
                    break;
                case 'balloonskirt':
                    $point += 0;
                    break;
                default:
                    // No points added
                    break;
            }

            $pattern = $data['pattern'] ?? $data["skirt_print"] ?? null;
            switch ($pattern) {
                case 'skirtsade':
                case 'skirtriz':
                case 'skirtamudi':
                case 'skirtofoghi':
                case 'skirtdorosht':
                    $point += 40;
                    break;
                default:
                    // No points added
                    break;
            }
        } elseif (($data['skirt_and_pants'] ?? null) === 'pants') {
            switch ($data['rise'] ?? null) {
                case 'highrise':
                    $point += 30;
                    break;
                case 'lowrise':
                    $point += 10;
                    break;
                default:
                    // No points added
                    break;
            }

            $type = $data['type'] ?? $data['shalvar'] ?? '';
            switch ($type) {
                case 'wskinny':
                case 'wbootcut':
                case 'wbaggy':
                case 'wstraight':
                    $point += 30;
                    break;
                case 'wcargo':
                case 'wshorts':
                case 'wcargoshorts':
                case 'wmom':
                    $point += 10;
                    break;
                default:
                    // No points added
                    break;
            }

            $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
            switch ($pattern) {
                case 'wpamudi':
                case 'wpofoghi':
                case 'wpriz':
                case 'wpdorosht':
                case 'wpsade':
                    $point += 20;
                    break;
                default:
                    // No points added
                    break;
            }
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 10 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 10 : 0;

        return $point;
    }


    public function womenBalataneTwoOne(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        switch ($collar) {
            case 'off_the_shoulder':
            case 'V_neck':
            case 'halter':
            case 'sweatheart':
            case 'one_shoulder':
                $point += 20;
                break;
            case 'turtleneck':
            case 'round':
            case 'hoodie':
            case 'classic':
                $point += 10;
                break;
            case 'squer':
            case 'boatneck':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        switch ($sleeve) {
            case 'toppuffy':
            case 'bottompuffy':
            case 'fshortsleeve':
                $point += 20;
                break;
            case 'flongsleeve':
                $point += 10;
                break;
            case 'fsleeveless':
            case 'fhalfsleeve':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        $point += isset($data["loose"]) ? $data["loose"] == "snatched" ? 10 : 0 : 0;
        $point += isset($data["wrap"]) ? $data["wrap"] == "wrap" ? 5 : 0 : 0;
        $point += isset($data["peplum"]) ? $data["peplum"] == "peplum" ? 5 : 0 : 0;
        $point += isset($data["belted"]) ? $data["belted"] == "belted" ? 5 : 0 : 0;
        $point += isset($data["cowl"]) ? $data["cowl"] == "cowl" ? 5 : 0 : 0;
        $point += isset($data["empire"]) ? $data["empire"] == "empire" ? 3 : 0 : 0;


        switch ($data['pattern'] ?? null) {
            case 'ofoghi':
            case 'dorosht':
            case 'riz':
                $point += 10;
                break;
            case 'sade':
                $point += 5;
                break;
            case 'amudi':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 10 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 10 : 0;

        return $point;
    }


    public function womenPayintaneTwoOne(array $data): int
    {
        $point = 0;

        if (($data['skirt_and_pants'] ?? null) === 'skirt') {
            $type = $data['skirt_type'] ?? '';
            switch ($type) {
                case 'wrapskirt':
                case 'balloonskirt':
                case 'alineskirt':
                    $point += 40;
                    break;
                case 'shortaskirt':
                    $point += 20;
                    break;
                case 'mermaidskirt':
                case 'miniskirt':
                case 'pencilskirt':
                    $point += 0;
                    break;
                default:
                    // No points added
                    break;
            }

            $pattern = $data['pattern'] ?? $data["skirt_print"] ?? null;
            switch ($pattern) {
                case 'skirtofoghi':
                case 'skirtriz':
                case 'skirtdorosht':
                    $point += 40;
                    break;
                case 'skirtsade':
                    $point += 20;
                    break;
                case 'skirtamudi':
                    $point += 0;
                    break;
                default:
                    // No points added
                    break;
            }
        } elseif (($data['skirt_and_pants'] ?? null) === 'pants') {
            switch ($data['rise'] ?? null) {
                case 'highrise':
                    $point += 30;
                    break;
                case 'lowrise':
                    $point += 10;
                    break;
                default:
                    // No points added
                    break;
            }

            $type = $data['type'] ?? $data['shalvar'] ?? '';
            switch ($type) {
                case 'wbootcut':
                case 'wmom':
                case 'wcargo':
                case 'wcargoshorts':
                    $point += 30;
                    break;
                case 'wbaggy':
                case 'wstraight':
                case 'wshorts':
                    $point += 10;
                    break;
                case 'wskinny':
                    $point += 0;
                    break;
                default:
                    // No points added
                    break;
            }

            $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
            switch ($pattern) {
                case 'wpofoghi':
                case 'wpriz':
                case 'wpdorosht':
                    $point += 20;
                    break;
                case 'wpamudi':
                    $point += 0;
                    break;
                case 'wpsade':
                    $point += 10;
                    break;
                default:
                    // No points added
                    break;
            }
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 10 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 10 : 0;

        return $point;
    }


    public function womenBalataneThreeOne(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        switch ($collar) {
            case 'one_shoulder':
            case 'V_neck':
            case 'halter':
            case 'round':
                $point += 20;
                break;
            case 'turtleneck':
            case 'sweatheart':
            case 'boatneck':
            case 'hoodie':
            case 'classic':
                $point += 10;
                break;
            case 'squer':
            case 'off_the_shoulder':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        switch ($sleeve) {
            case 'fshortsleeve':
            case 'flongsleeve':
            case 'bottompuffy':
                $point += 20;
                break;
            case 'fhalfsleeve':
            case 'fsleeveless':
                $point += 10;
                break;
            case 'toppuffy':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }


        $point += isset($data["loose"]) ? $data["loose"] == "snatched" ? 10 : 0 : 0;
        $point += isset($data["wrap"]) ? $data["wrap"] == "wrap" ? 5 : 0 : 0;
        $point += isset($data["peplum"]) ? $data["peplum"] == "peplum" ? 5 : 0 : 0;
        $point += isset($data["belted"]) ? $data["belted"] == "belted" ? 5 : 0 : 0;
        $point += isset($data["empire"]) ? $data["empire"] == "empire" ? 3 : 0 : 0;


        switch ($data['pattern'] ?? null) {
            case 'amudi':
            case 'riz':
            case 'sade':
                $point += 10;
                break;
            case 'ofoghi':
            case 'dorosht':
                $point += 0;
                break;
            default:
                // No points added
                break;
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 10 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 0 : 0;

        return $point;
    }


    public function womenPayintaneThreeOne(array $data): int
    {
        $point = 0;

        if (($data['skirt_and_pants'] ?? null) === 'skirt') {
            $type = $data['skirt_type'] ?? null;

            if (in_array($type, ['balloonskirt', 'alineskirt', 'shortaskirt', 'wrapskirt'])) {
                $point += 40;
            } elseif (($type) === 'mermaidskirt') {
                $point += 20;
            } elseif (in_array($type, ['pencilskirt', 'miniskirt'])) {
                $point += 0;
            }

            $pattern = $data['pattern'] ?? $data["skirt_print"] ?? null;
            if (in_array($pattern, ['skirtofoghi', 'skirtriz', 'skirtdorosht'])) {
                $point += 40;
            } elseif (($pattern) === 'skirtamudi') {
                $point += 20;
            } elseif (in_array($pattern, ['skirtriz', 'skirtsade'])) {
                $point += 0;
            }
        } elseif (($data['skirt_and_pants'] ?? null) === 'pants') {
            if (($data['rise'] ?? null) === 'highrise') {
                $point += 30;
            } elseif (($data['rise'] ?? null) === 'lowrise') {
                $point += 10;
            }

            $type = $data['type'] ?? $data['shalvar'] ?? null;
            if (in_array($type, ['wbaggy', 'wcargo', 'wcargoshorts', 'wbootcut', 'wmom'])) {
                $point += 30;
            } elseif (in_array($type, ['wstraight', 'wshorts'])) {
                $point += 10;
            } elseif (($type) === 'wskinny') {
                $point += 0;
            }

            $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? null;
            if (in_array($pattern, ['wpofoghi', 'wpriz', 'wpdorosht'])) {
                $point += 20;
            } elseif (($pattern) === 'wpamudi') {
                $point += 10;
            } elseif (in_array($pattern, ['wpriz', 'wpsade'])) {
                $point += 0;
            }
        }


        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 0 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 10 : 0;

        return $point;

    }


    public function womenBalataneFourOne(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        if (in_array($collar, ['boatneck', 'round', 'sweatheart', 'off_the_shoulder', 'classic', 'hoodie'])) {
            $point += 20;
        } elseif (in_array($collar, ['squer', 'turtleneck', 'one_shoulder'])) {
            $point += 10;
        } elseif (in_array($collar, ['halter', 'V_neck'])) {
            $point += 0;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        if (in_array($sleeve, ['toppuffy', 'fhalfsleeve', 'fshortsleeve'])) {
            $point += 20;
        } elseif (($sleeve) === 'fsleeveless') {
            $point += 10;
        } elseif (in_array($sleeve, ['flongsleeve', 'bottompuffy'])) {
            $point += 0;
        }

        $point += isset($data["loose"]) ? $data["loose"] == "snatched" ? 10 : 0 : 0;
        $point += isset($data["wrap"]) ? $data["wrap"] == "wrap" ? 5 : 0 : 0;
        $point += isset($data["cowl"]) ? $data["cowl"] == "cowl" ? 5 : 0 : 0;
        $point += isset($data["belted"]) ? $data["belted"] == "belted" ? 5 : 0 : 0;
        $point += isset($data["peplum"]) ? $data["peplum"] == "peplum" ? 3 : 0 : 0;
        $point += isset($data["empire"]) ? $data["empire"] == "empire" ? 3 : 0 : 0;


        if (in_array($data['pattern'] ?? null, ['ofoghi', 'dorosht'])) {
            $point += 10;
        } elseif (in_array($data['pattern'] ?? null, ['amudi'])) {
            $point += 5;
        } elseif (in_array($data['pattern'] ?? null, ['riz', 'sade'])) {
            $point += 0;
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 0 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 10 : 0;

        return $point;

    }

    public function womenPayintaneFourOne(array $data): int
    {
        $point = 0;

        if (($data['skirt_and_pants'] ?? null) === 'skirt') {
            $type = $data['skirt_type'] ?? '';
            switch ($type) {
                case 'alineskirt':
                case 'pencilskirt':
                case 'wrapskirt':
                    $point += 40;
                    break;
                case 'mermaidskirt':
                case 'shortaskirt':
                    $point += 20;
                    break;
                case 'balloonskirt':
                case 'miniskirt':
                    $point += 0;
                    break;
                default:
                    // No points added
                    break;
            }

            $pattern = $data['pattern'] ?? $data["skirt_print"] ?? null;
            switch ($pattern) {
                case 'skirtamudi':
                case 'skirtriz':
                case 'skirtsade':
                    $point += 40;
                    break;
                case 'skirtofoghi':
                case 'skirtdorosht':
                    $point += 0;
                    break;
                default:
                    // No points added
                    break;
            }
        } elseif (($data['skirt_and_pants'] ?? null) === 'pants') {
            if (($data['rise'] ?? null) === 'highrise') {
                $point += 30;
            } elseif (($data['rise'] ?? null) === 'lowrise') {
                $point += 0;
            }

            $type = $data['type'] ?? $data['shalvar'] ?? null;
            if (in_array($type, ['wskinny', 'wbaggy', 'wbootcut', 'wstraight'])) {
                $point += 30;
            } elseif (($type) === 'wshorts') {
                $point += 10;
            } elseif (in_array($type, ['wmom', 'wcargo', 'wcargoshorts'])) {
                $point += 0;
            }

            $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
            switch ($pattern) {
                case 'wpamudi':
                case 'wpriz':
                case 'wpsade':
                    $point += 20;
                    break;
                case 'wpofoghi':
                case 'wpdorosht':
                    $point += 0;
                    break;
                default:
                    // No points added
                    break;
            }
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 10 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 0 : 0;

        return $point;

    }


    public function womenBalataneFiveOne(array $data): int
    {
        $point = 0;

        $collar = $data['collar'] ?? $data['yaghe'] ?? null;
        if (in_array($collar, ['one_shoulder', 'off_the_shoulder', 'V_neck', 'squer'])) {
            $point += 20;
        } elseif (in_array($collar, ['round', 'sweatheart', 'boatneck', 'hoodie', 'classic'])) {
            $point += 10;
        } elseif (in_array($collar, ['halter', 'turtleneck'])) {
            $point += 0;
        }

        $sleeve = $data['sleeve'] ?? $data['astin'] ?? null;
        if (in_array($sleeve, ['fshortsleeve', 'flongsleeve', 'fsleeveless', 'bottompuffy'])) {
            $point += 20;
        } elseif (in_array($sleeve, ['fhalfsleeve', 'toppuffy'])) {
            $point += 0;
        }


        $point += isset($data["wrap"]) ? $data["wrap"] == "wrap" ? 5 : 0 : 0;
        $point += isset($data["belted"]) ? $data["belted"] == "belted" ? 5 : 0 : 0;
        $point += isset($data["peplum"]) ? $data["peplum"] == "peplum" ? 5 : 0 : 0;
        $point += isset($data["empire"]) ? $data["empire"] == "empire" ? 5 : 0 : 0;
        $point += isset($data["loose"]) ? $data["loose"] == "loose" || $data["loose"] == "losse" ? 5 : 0 : 0;
        $point += isset($data["cowl"]) ? $data["cowl"] == "cowl" ? 3 : 0 : 0;


        if (in_array($data['pattern'] ?? null, ['amudi', 'riz', 'sade'])) {
            $point += 10;
        } elseif (in_array($data['pattern'] ?? null, ['dorosht', 'ofoghi'])) {
            $point += 0;
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 10 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 0 : 0;

        return $point;
    }


    public function womenPayintaneFiveOne(array $data): int
    {
        $point = 0;

        if (($data['skirt_and_pants'] ?? null) === 'skirt') {
            $type = $data['skirt_type'] ?? '';
            switch ($type) {
                case 'wrapskirt':
                case 'pencilskirt':
                case 'shortaskirt':
                    $point += 40;
                    break;
                case 'alineskirt':
                case 'miniskirt':
                    $point += 20;
                    break;
                case 'mermaidskirt':
                case 'balloonskirt':
                    $point += 0;
                    break;
                default:
                    // No points added
                    break;
            }

            $pattern = $data['pattern'] ?? $data["skirt_print"] ?? null;
            switch ($pattern) {
                case 'skirtofoghi':
                case 'skirtdorosht':
                    $point += 40;
                    break;
                case 'skirtsade':
                    $point += 20;
                    break;
                case 'skirtriz':
                case 'skirtamudi':
                    $point += 0;
                    break;
                default:
                    // No points added
                    break;
            }
        } elseif (($data['skirt_and_pants'] ?? null) === 'pants') {
            if (($data['rise'] ?? null) === 'highrise') {
                $point += 30;
            } elseif (($data['rise'] ?? null) === 'lowrise') {
                $point += 0;
            }

            $type = $data['type'] ?? $data['shalvar'] ?? null;
            if (in_array($type, ['wbaggy', 'wstraight', 'wmom', 'wshorts'])) {
                $point += 30;
            } elseif (in_array($type, ['wbootcut', 'wcargo', 'wcargoshorts'])) {
                $point += 10;
            } elseif (($type) === 'wskinny') {
                $point += 0;
            }

            $pattern = $data['pattern'] ?? $data['tarh_shalvar'] ?? '';
            switch ($pattern) {
                case 'wpofoghi':
                case 'wpdorosht':
                    $point += 20;
                    break;
                case 'wpamudi':
                    $point += 0;
                    break;
                case 'wpsade':
                    $point += 10;
                    break;
                default:
                    // No points added
                    break;
            }
        }

        // Color
        $color = $data['color'] ?? $data['color_tone'] ?? '';
        $tones = explode('_', $color);
        $point += (in_array('muted', $tones) || in_array('dark', $tones)) ? 0 : 0;
        $point += (in_array('bright', $tones) || in_array('light', $tones)) ? 10 : 0;

        return $point;
    }

}
