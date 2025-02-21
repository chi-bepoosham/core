<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function matchWithOtherClothes(): void
    {
        $currentProcessedImageData = json_decode($this->processed_image_data);
        $userBodyType = $this->user->bodyType->predict_value;
        $otherClothes = UserClothes::query()->where("user_id", $this->user_id)->get();

        foreach ($otherClothes as $clothe) {
            $clotheProcessedImageData = json_decode($clothe->processed_image_data);
            $matched = false;

            if ($clothe->match_percentage != null && $clothe->match_percentage >= 50) {

                $mnistPrediction = $currentProcessedImageData?->mnist_prediction ?? '';
                $mnistPrediction = strtolower($mnistPrediction);
                $clotheMnistPrediction = $clotheProcessedImageData?->mnist_prediction ?? '';
                $clotheMnistPrediction = strtolower($clotheMnistPrediction);

                $color = $currentProcessedImageData?->color_tone ?? '';
                $clotheColor = $clotheProcessedImageData?->color_tone ?? '';

                if ($currentProcessedImageData->paintane == 'ftamamtane' && $clotheMnistPrediction == 'over') {

                    $shalvar = $currentProcessedImageData?->shalvar ?? '';
                    $shalvar = strtolower($shalvar);

                    $skirtType = $currentProcessedImageData?->skirt_type ?? '';
                    $skirtType = strtolower($skirtType);

                    $tarh = $currentProcessedImageData?->tarh_shalvar ?? $currentProcessedImageData?->skirt_print ?? '';
                    $tarh = strtolower($tarh);

                    $clothePattren = $clotheProcessedImageData?->pattren ?? '';
                    $clothePattren = strtolower($clothePattren);

                    $clotheAstin = $clotheProcessedImageData?->astin ?? '';
                    $clotheAstin = strtolower($clotheAstin);

                    switch ($userBodyType) {
                        case 'women_hourglass':

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

                            } else {
                                $matched = true;
                            }

                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($clothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'women_rectangle':

                            if ($shalvar == 'wbootcut' || $shalvar == 'wbaggy' || $skirtType == 'balloonskirt') {
                                if ($clotheAstin == 'toppuffy') {
                                    $matched = true;
                                }
                            } elseif ($shalvar == 'wstraight' || $skirtType == 'alineskirt' || $skirtType == 'shortaskirt' || $skirtType == 'wrapskirt') {

                                if ($clotheAstin == 'fsleeveless' || $clotheAstin == 'fshortsleeve' || $clotheAstin == 'flongsleeve' ||
                                    $clotheAstin == 'fhalfsleeve' || $clotheAstin == 'bottompuffy') {

                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($clothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'women_inverted_triangle':

                            if ($shalvar == 'wskinny' || $shalvar == 'wstraight' ||
                                $shalvar == 'wshorts' || $skirtType == 'alineskirt' || $skirtType == 'pencilskirt' ||
                                $skirtType == 'shortaskirt') {
                                if ($clotheAstin == 'flongsleeve' || $clotheAstin == 'bottompuffy' ||
                                    $clotheAstin == 'fsleeveless' || $clotheAstin == 'fhalfsleeve') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($clothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted' || $clotheColor = 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }


                            }

                            break;
                        case 'women_triangle':
                            if ($shalvar == 'wbaggy' || $shalvar == 'wcargo' ||
                                $shalvar == 'wcargoshorts' || $shalvar == 'wbootcut' ||
                                $shalvar == 'wmom' || $skirtType == 'balloonskirt' || $skirtType == 'mermaidskirt') {

                                if ($clotheAstin == 'fshortsleeve' || $clotheAstin == 'toppuffy') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($clothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_bright') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;
                        case 'women_round':
                            if ($shalvar == 'wskinny' || $shalvar == 'wstraight' ||
                                $shalvar == 'wshorts' || $skirtType == 'alineskirt' || $skirtType == 'pencilskirt' ||
                                $skirtType == 'shortaskirt') {
                                if ($clotheAstin == 'flongsleeve' || $clotheAstin == 'bottompuffy' ||
                                    $clotheAstin == 'fsleeveless' || $clotheAstin == 'fhalfsleeve') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($clothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted'
                                        || $clotheColor = 'light_muted' || $clotheColor == 'light_bright') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted' || $clotheColor == 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }


                            }

                            break;
                    }


                }elseif ($currentProcessedImageData->paintane == 'fpayintane' && $clotheProcessedImageData->paintane == 'fbalatane') {

                    if ($clotheMnistPrediction == 'over') {
                        // todo check multiple clothes
                    }

                    $shalvar = $currentProcessedImageData?->shalvar ?? '';
                    $shalvar = strtolower($shalvar);

                    $skirtType = $currentProcessedImageData?->skirt_type ?? '';
                    $skirtType = strtolower($skirtType);

                    $tarh = $currentProcessedImageData?->tarh_shalvar ?? $currentProcessedImageData?->skirt_print ?? '';
                    $tarh = strtolower($tarh);

                    $clothePattren = $clotheProcessedImageData?->pattren ?? '';
                    $clothePattren = strtolower($clothePattren);

                    $clotheAstin = $clotheProcessedImageData?->astin ?? '';
                    $clotheAstin = strtolower($clotheAstin);

                    switch ($userBodyType) {
                        case 'women_hourglass':

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

                            } else {
                                $matched = true;
                            }

                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($clothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'women_rectangle':

                            if ($shalvar == 'wbootcut' || $shalvar == 'wbaggy' || $skirtType == 'balloonskirt') {
                                if ($clotheAstin == 'toppuffy') {
                                    $matched = true;
                                }
                            } elseif ($shalvar == 'wstraight' || $skirtType == 'alineskirt' || $skirtType == 'shortaskirt' || $skirtType == 'wrapskirt') {

                                if ($clotheAstin == 'fsleeveless' || $clotheAstin == 'fshortsleeve' || $clotheAstin == 'flongsleeve' ||
                                    $clotheAstin == 'fhalfsleeve' || $clotheAstin == 'bottompuffy') {

                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($clothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'women_inverted_triangle':

                            if ($shalvar == 'wskinny' || $shalvar == 'wstraight' ||
                                $shalvar == 'wshorts' || $skirtType == 'alineskirt' || $skirtType == 'pencilskirt' ||
                                $skirtType == 'shortaskirt') {
                                if ($clotheAstin == 'flongsleeve' || $clotheAstin == 'bottompuffy' ||
                                    $clotheAstin == 'fsleeveless' || $clotheAstin == 'fhalfsleeve') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($clothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted' || $clotheColor = 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }


                            }

                            break;
                        case 'women_triangle':
                            if ($shalvar == 'wbaggy' || $shalvar == 'wcargo' ||
                                $shalvar == 'wcargoshorts' || $shalvar == 'wbootcut' ||
                                $shalvar == 'wmom' || $skirtType == 'balloonskirt' || $skirtType == 'mermaidskirt') {

                                if ($clotheAstin == 'fshortsleeve' || $clotheAstin == 'toppuffy') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($clothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_bright') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;
                        case 'women_round':
                            if ($shalvar == 'wskinny' || $shalvar == 'wstraight' ||
                                $shalvar == 'wshorts' || $skirtType == 'alineskirt' || $skirtType == 'pencilskirt' ||
                                $skirtType == 'shortaskirt') {
                                if ($clotheAstin == 'flongsleeve' || $clotheAstin == 'bottompuffy' ||
                                    $clotheAstin == 'fsleeveless' || $clotheAstin == 'fhalfsleeve') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($clothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted'
                                        || $clotheColor = 'light_muted' || $clotheColor == 'light_bright') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted' || $clotheColor == 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }


                            }

                            break;
                    }

                } elseif ($currentProcessedImageData->paintane == 'fbalatane') {
//
                    if ($mnistPrediction == 'under' && $clotheMnistPrediction == 'under') {
                        continue;
                    }
                    if ($mnistPrediction == 'over' && $clotheMnistPrediction == 'over') {
                        continue;
                    }

                    if ($mnistPrediction == 'under' && $clotheProcessedImageData->paintane == 'ftamamtane') {
                        continue;
                    }

                    if ($mnistPrediction == 'under' && $clotheMnistPrediction == 'over') {
                        // todo check multiple clothes
                    }

                    if ($mnistPrediction == 'over' && $clotheMnistPrediction == 'under') {
                        // todo check multiple clothes
                    }

                    if ($mnistPrediction == 'over' && $clotheProcessedImageData->paintane == 'fpaintane') {
                        // todo check multiple clothes
                    }


                    $clotheShalvar = $clotheProcessedImageData?->shalvar ?? '';
                    $clotheShalvar = strtolower($clotheShalvar);

                    $clotheSkirtType = $clotheProcessedImageData?->skirt_type ?? '';
                    $clotheSkirtType = strtolower($clotheSkirtType);

                    $clotheTarh = $clotheProcessedImageData?->tarh_shalvar ?? $clotheProcessedImageData?->skirt_print ?? '';
                    $clotheTarh = strtolower($clotheTarh);

                    $pattren = $currentProcessedImageData?->pattren ?? '';
                    $pattren = strtolower($pattren);

                    $astin = $currentProcessedImageData?->astin ?? '';
                    $astin = strtolower($astin);

                    switch ($userBodyType) {
                        case 'women_hourglass':
                            if ($astin == 'toppuffy') {
                                if ($clotheShalvar == 'wbootcut' || $clotheShalvar == 'wbaggy' || $clotheSkirtType == 'balloonskirt') {
                                    $matched = true;
                                }
                            } elseif ($astin == 'fsleeveless' || $astin == 'fshortsleeve' || $astin == 'flongsleeve' ||
                                $astin == 'fhalfsleeve' || $astin == 'bottompuffy') {

                                if ($clotheShalvar == 'wskinny' || $clotheShalvar == 'wstraight' ||
                                    $clotheSkirtType == 'mermaidskirt' || $clotheSkirtType == 'alineskirt' ||
                                    $clotheSkirtType == 'shortaskirt' || $clotheSkirtType == 'pencilskirt' ||
                                    $clotheSkirtType == 'wrapskirt' || $clotheSkirtType == 'miniskirt') {

                                    $matched = true;
                                }

                            } else {
                                $matched = true;
                            }

                            if ($pattren == 'sade') {
                                $matched = true;
                            } else {
                                if ($clotheTarh == 'sade' || $clotheTarh == 'skirtsade') {
                                    $matched = true;
                                }
                            }


                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'women_rectangle':

                            if ($astin == 'toppuffy') {
                                if ($clotheShalvar == 'wbootcut' || $clotheShalvar == 'wbaggy' || $clotheSkirtType == 'balloonskirt') {
                                    $matched = true;
                                }
                            } elseif ($astin == 'fsleeveless' || $astin == 'fshortsleeve' || $astin == 'flongsleeve' ||
                                $astin == 'fhalfsleeve' || $astin == 'bottompuffy') {

                                if ($clotheShalvar == 'wstraight' || $clotheShalvar == 'wbaggy' ||
                                    $clotheSkirtType == 'alineskirt' || $clotheSkirtType == 'shortaskirt' ||
                                    $clotheSkirtType == 'wrapskirt') {

                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($pattren == 'sade') {
                                $matched = true;
                            } else {
                                if ($clotheTarh == 'sade' || $clotheTarh == 'skirtsade') {
                                    $matched = true;
                                }
                            }


                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'women_inverted_triangle':

                            if ($astin == 'fshortsleeve' || $astin == 'toppuffy') {
                                if ($clotheShalvar == 'wbaggy' || $clotheShalvar == 'wcargo' ||
                                    $clotheShalvar == 'wcargoshorts' || $clotheShalvar == 'wbootcut' ||
                                    $clotheShalvar == 'wmom' || $clotheSkirtType == 'balloonskirt' ||
                                    $clotheSkirtType == 'mermaidskirt') {

                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($pattren == 'sade') {
                                $matched = true;
                            } else {
                                if ($clotheTarh == 'sade' || $clotheTarh == 'skirtsade') {
                                    $matched = true;
                                }
                            }

                            if ($color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_bright') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;
                        case 'women_triangle':

                            if ($astin == 'flongsleeve' || $astin == 'bottompuffy' ||
                                $astin == 'fsleeveless' || $astin == 'fhalfsleeve') {
                                if ($clotheShalvar == 'wskinny' || $clotheShalvar == 'wstraight' ||
                                    $clotheShalvar == 'wshorts' || $clotheSkirtType == 'alineskirt' || $clotheSkirtType == 'pencilskirt' ||
                                    $clotheSkirtType == 'shortaskirt') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($pattren == 'sade') {
                                $matched = true;
                            } else {
                                if ($clotheTarh == 'sade' || $clotheTarh == 'skirtsade') {
                                    $matched = true;
                                }
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted' || $clotheColor = 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }


                            }

                            break;
                        case 'women_round':

                            if ($astin == 'fshortsleeve' || $astin == 'toppuffy') {
                                if ($clotheShalvar == 'wbaggy' || $clotheShalvar == 'wcargo' ||
                                    $clotheShalvar == 'wcargoshorts' || $clotheShalvar == 'wmom' || $clotheShalvar == 'wshorts' ||
                                    $clotheSkirtType == 'balloonskirt' || $clotheSkirtType == 'shortaskirt') {

                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($pattren == 'sade') {
                                $matched = true;
                            } else {
                                if ($clotheTarh == 'sade' || $clotheTarh == 'skirtsade') {
                                    $matched = true;
                                }
                            }

                            if ($color == 'dark_bright') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted'
                                    || $clotheColor = 'light_muted' || $clotheColor == 'light_bright') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;
                    }


                } elseif ($currentProcessedImageData->paintane == 'mpayintane' && $clotheProcessedImageData->paintane == 'mbalatane') {

                    if ($clotheMnistPrediction == 'over') {
                        // todo check multiple clothes
                    }

                    $shalvar = $currentProcessedImageData?->shalvar ?? '';
                    $shalvar = strtolower($shalvar);

                    $tarh = $currentProcessedImageData?->tarh_shalvar ?? '';
                    $tarh = strtolower($tarh);

                    $clothePattren = $clotheProcessedImageData?->pattren ?? '';
                    $clothePattren = strtolower($clothePattren);

                    $clotheAstin = $clotheProcessedImageData?->astin ?? '';
                    $clotheAstin = strtolower($clotheAstin);

                    switch ($userBodyType) {
                        case 'men_rectangle':

                            if ($shalvar == 'mcargo' || $shalvar == 'mcargoshorts' || $shalvar == 'mmom') {
                                if ($clotheAstin == 'shortsleeve') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($tarh != 'mpsade') {
                                if ($clothePattren == 'mpsade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_bright') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'men_inverted_triangle':

                            if ($shalvar == 'mslimfit' || $shalvar == 'mshorts') {
                                if ($clotheAstin == 'longsleeve') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($tarh != 'mpsade') {
                                if ($clothePattren == 'mpsade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted' || $clotheColor = 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }


                            }

                            break;

                        case 'men_oval':

                            if ($shalvar == 'mslimfit' || $shalvar == 'mstraight') {
                                if ($clotheAstin == 'longsleeve') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($tarh != 'mpsade') {
                                if ($clothePattren == 'mpsade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted'
                                        || $clotheColor = 'light_muted' || $clotheColor == 'light_bright') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted' || $clotheColor == 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }


                            }

                            break;
                    }

                } elseif ($currentProcessedImageData->paintane == 'mbalatane') {

                    if ($mnistPrediction == 'under' && $clotheMnistPrediction == 'under') {
                        continue;
                    }
                    if ($mnistPrediction == 'over' && $clotheMnistPrediction == 'over') {
                        continue;
                    }

                    if ($mnistPrediction == 'under' && $clotheMnistPrediction == 'over') {
                        // todo check multiple clothes
                    }

                    if ($mnistPrediction == 'over' && $clotheMnistPrediction == 'under') {
                        // todo check multiple clothes
                    }

                    if ($mnistPrediction == 'over' && $clotheProcessedImageData->paintane == 'mpayintane') {
                        // todo check multiple clothes
                    }

                    $clotheShalvar = $clotheProcessedImageData?->shalvar ?? '';
                    $clotheShalvar = strtolower($clotheShalvar);

                    $clotheTarh = $clotheProcessedImageData?->tarh_shalvar ?? '';
                    $clotheTarh = strtolower($clotheTarh);

                    $pattren = $currentProcessedImageData?->pattren ?? '';
                    $pattren = strtolower($pattren);

                    $astin = $currentProcessedImageData?->astin ?? '';
                    $astin = strtolower($astin);


                    switch ($userBodyType) {
                        case 'men_rectangle':
                            if ($astin == 'longsleeve') {
                                if ($clotheShalvar == 'mshorts' || $clotheShalvar == 'mslimfit' || $clotheShalvar == 'mstraight') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($pattren == 'mpsade') {
                                $matched = true;
                            } else {
                                if ($clotheTarh == 'mpsade') {
                                    $matched = true;
                                }
                            }


                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted' || $clotheColor = 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }
                            }

                            break;

                        case 'men_inverted_triangle':
                            if ($astin == 'shortsleeve') {
                                if ($clotheShalvar == 'mcargo' || $clotheShalvar == 'mcargoshorts' || $clotheShalvar == 'mmom') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($pattren == 'mpsade') {
                                $matched = true;
                            } else {
                                if ($clotheTarh == 'mpsade') {
                                    $matched = true;
                                }
                            }


                            if ($color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_bright') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'men_oval':
                            if ($astin == 'longsleeve') {
                                if ($clotheShalvar == 'mshorts' || $clotheShalvar == 'mslimfit' || $clotheShalvar == 'mstraight') {
                                    $matched = true;
                                }
                            } elseif ($astin == 'shortsleeve') {
                                if ($clotheShalvar == 'mcargo' || $clotheShalvar == 'mcargoshorts' || $clotheShalvar == 'mmom' ||
                                    $clotheShalvar == 'mstraight' || $clotheShalvar == 'mshorts') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($pattren == 'mpsade') {
                                $matched = true;
                            } else {
                                if ($clotheTarh == 'mpsade') {
                                    $matched = true;
                                }
                            }

                            if ($color == 'dark_bright') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_muted') {
                                if ($clotheColor == 'dark_bright' || $clotheColor == 'dark_muted'
                                    || $clotheColor = 'light_muted' || $clotheColor == 'light_bright') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($clotheColor == 'light_bright' || $clotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;
                    }

                }


                if ($matched) {
                    UserClothesPivot::query()->insert(
                        [
                            "first_user_clothes_id" => $this->id,
                            "second_user_clothes_id" => $clothe->id,
                            "matched" => true
                        ]
                    );
                    UserClothesPivot::query()->insert(
                        [
                            "first_user_clothes_id" => $clothe->id,
                            "second_user_clothes_id" => $this->id,
                            "matched" => true
                        ]
                    );
                }
            }
        }
    }


    public function matchClothes($matchedIds)
    {


    }
}
