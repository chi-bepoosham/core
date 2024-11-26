<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class UserClothes extends Model
{
    protected $table = 'user_clothes';

    protected $fillable = [
        'user_id',
        'image',
        'match_percentage',
        'clothes_type',
        'process_status',
        'processed_image_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'image' => 'string',
            'match_percentage' => 'integer',
            'clothes_type' => 'integer',
            'process_status' => 'integer',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }

    protected $hidden = ["processed_image_data"];

    public function matchedClothing(): BelongsToMany
    {
        return $this->belongsToMany(UserClothes::class, "user_clothes_pivot", "first_user_clothes_id", "second_user_clothes_id")->withPivot('matched');
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function matchWithOtherClothes()
    {
        $currentProcessedImageData = $this->processed_image_data;
        $userBodyType = (int)$this->user->bodyType->predict_value;
        $otherClothes = UserClothes::query()->where("user_id", $this->user_id)->get();

        foreach ($otherClothes as $clothe) {
            $clotheProcessedImageData = $clothe->processed_image_data;
            $matched = false;

            if ($clotheProcessedImageData->match_percentage >= 50) {

                if ($currentProcessedImageData->paintane == 'fpayintane' && $clotheProcessedImageData->paintane != 'fpayintane') {

                    $mnistPrediction = $clotheProcessedImageData?->mnist_prediction ?? '';
                    $mnistPrediction = strtolower($mnistPrediction);
                    if ($mnistPrediction == 'jacket' || $mnistPrediction = 't-shirt' ||
                            $mnistPrediction == 'shirt' || $mnistPrediction == 'pullover') {


                        $currentColor = $currentProcessedImageData?->color_tone ?? '';
                        $clotheColor = $clotheProcessedImageData?->color_tone ?? '';

                        $shalvar = $currentProcessedImageData?->shalvar ?? '';
                        $shalvar = strtolower($shalvar);

                        $skirtType = $currentProcessedImageData?->skirt_type ?? '';
                        $skirtType = strtolower($skirtType);

                        $tarh = $currentProcessedImageData?->tarh_shalvar ?? $currentProcessedImageData?->skirt_print ?? '';
                        $tarh = strtolower($tarh);

                        $clotheTarh = $clotheProcessedImageData?->tarh_shalvar ?? $clotheProcessedImageData?->skirt_print ?? '';
                        $clotheTarh = strtolower($clotheTarh);

                        $clotheAstin = $clotheProcessedImageData?->astin ?? '';
                        $clotheAstin = strtolower($clotheAstin);

                        switch ($userBodyType){
                            case 11:

                                if ($shalvar == 'wbootcut' || $shalvar == 'wbaggy' || $skirtType == 'balloonskirt') {

                                    if ($clotheAstin == 'toppuffy') {
                                        $matched = true;
                                    }
                                } elseif ($shalvar == 'wskinny' || $shalvar == 'wstraight' ||
                                    $skirtType == 'mermaidskirt' || $skirtType == 'alineskirt' ||
                                    $skirtType == 'shortaskirt' || $skirtType == 'pencilskirt' ||
                                    $skirtType == 'wrapskirt' || $skirtType == 'miniskirt') {

                                    if ($clotheAstin == 'fsleeveless' || $clotheAstin == 'fshortsleeve' || $clotheAstin == 'flongsleeve' ||
                                        $clotheAstin == 'fhalfsleeve' || $clotheAstin == 'bottompuffy') {

                                        $matched = true;
                                    }

                                }

                                if ($tarh != 'sade') {
                                    if ($clotheTarh == 'sade') {
                                        $matched = true;
                                    }
                                } else {
                                    $matched = true;
                                }

                                if ($currentColor == 'dark_bright' || $currentColor == 'dark_muted') {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } elseif ($currentColor == 'light_bright' || $currentColor == 'light_muted') {
                                    if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }

                                break;

                            case 21:

                                break;
                            case 31:

                                break;
                            case 41:

                                break;
                            case 51:

                                break;
                        }


                    }
                }


            }


            if ($matched) {
                UserClothesPivot::query()->insert(
                    [
                        "first_user_clothes_id" => $currentProcessedImageData->id,
                        "second_user_clothes_id" => $clotheProcessedImageData->id,
                        "matched" => true
                    ]
                );
                UserClothesPivot::query()->insert(
                    [
                        "first_user_clothes_id" => $clotheProcessedImageData->id,
                        "second_user_clothes_id" => $currentProcessedImageData->id,
                        "matched" => true
                    ]
                );
            }
        }
    }
}
