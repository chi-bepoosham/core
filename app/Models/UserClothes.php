<?php

namespace App\Models;

use App\Http\Repositories\UserSetRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class UserClothes extends Model
{
    use SoftDeletes;

    protected $table = 'user_clothes';

    protected $fillable = [
        'user_id',
        'image',
        'title',
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
            'title' => 'string',
            'match_percentage' => 'integer',
            'clothes_type' => 'integer',
            'process_status' => 'integer',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
            'deleted_at' => 'timestamp',
        ];
    }

    protected $hidden = ["processed_image_data"];

    public function sets(): HasMany
    {
        return $this->hasMany(UserSet::class, 'user_id', 'id')->with("clothes");
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


                } elseif ($currentProcessedImageData->paintane == 'fpayintane' && $clotheProcessedImageData->paintane == 'fbalatane') {

                    if ($clotheMnistPrediction == 'over') {
                        $this->matchMultipleClothes($this, $clothe, $otherClothes, null, 'under');
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
                        $this->matchMultipleClothes($clothe, $this, $otherClothes, 'fpaintane');
                    }

                    if ($mnistPrediction == 'over' && $clotheMnistPrediction == 'under') {
                        $this->matchMultipleClothes($this, $clothe, $otherClothes, 'fpaintane');
                    }

                    if ($mnistPrediction == 'over' && $clotheProcessedImageData->paintane == 'fpaintane') {
                        $this->matchMultipleClothes($clothe, $this, $otherClothes, null, 'under');
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
                        $this->matchMultipleClothes($this, $clothe, $otherClothes, null, 'under');
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
                        $this->matchMultipleClothes($clothe, $this, $otherClothes, 'mpayintane');
                    }

                    if ($mnistPrediction == 'over' && $clotheMnistPrediction == 'under') {
                        $this->matchMultipleClothes($this, $clothe, $otherClothes, 'mpayintane');
                    }

                    if ($mnistPrediction == 'over' && $clotheProcessedImageData->paintane == 'mpayintane') {
                        $this->matchMultipleClothes($clothe, $this, $otherClothes, null, 'under');
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


                #match clothes
                if ($matched) {
                    $userSet = $this->createSet($this->user_id);
                    $this->attachUserClothesSet($userSet, [$this->id, $clothe->id]);
                }
            }
        }
    }


    public function matchMultipleClothes($currentClothes, $secondClothe, $allUserClothes, $needPaintane = null, $needMnistPrediction = null)
    {
        $currentProcessedImageData = json_decode($currentClothes->processed_image_data);
        $secondClotheProcessedImageData = json_decode($secondClothe->processed_image_data);

        $userBodyType = $currentClothes->user->bodyType->predict_value;

        $mnistPrediction = $currentProcessedImageData?->mnist_prediction ?? '';
        $mnistPrediction = strtolower($mnistPrediction);
        $secondClotheMnistPrediction = $secondClotheProcessedImageData?->mnist_prediction ?? '';
        $secondClotheMnistPrediction = strtolower($secondClotheMnistPrediction);

        $color = $currentProcessedImageData?->color_tone ?? '';
        $secondClotheColor = $clotheProcessedImageData?->color_tone ?? '';


        foreach ($allUserClothes as $userClothe) {
            $userClotheProcessedImageData = json_decode($userClothe->processed_image_data);
            $matched = false;

            if ($needPaintane != null || $needMnistPrediction != null) {
                $userClotheMnistPrediction = $userClotheProcessedImageData?->mnist_prediction ?? '';
                $userClotheMnistPrediction = strtolower($userClotheMnistPrediction);
                $userClotheColor = $userClotheProcessedImageData?->color_tone ?? '';

                if ($needPaintane != $userClotheProcessedImageData->paintane) {
                    continue;
                }

                if ($needMnistPrediction != $userClotheMnistPrediction) {
                    continue;
                }

                if ($currentProcessedImageData->paintane == 'fpayintane' && $secondClotheProcessedImageData->paintane == 'fbalatane') {

                    $shalvar = $currentProcessedImageData?->shalvar ?? '';
                    $shalvar = strtolower($shalvar);

                    $skirtType = $currentProcessedImageData?->skirt_type ?? '';
                    $skirtType = strtolower($skirtType);

                    $tarh = $currentProcessedImageData?->tarh_shalvar ?? $currentProcessedImageData?->skirt_print ?? '';
                    $tarh = strtolower($tarh);

                    $secondClotheTarh = $secondClotheProcessedImageData?->tarh_shalvar ?? $currentProcessedImageData?->skirt_print ?? '';
                    $secondClotheTarh = strtolower($secondClotheTarh);

                    $userClothePattren = $userClotheProcessedImageData?->pattren ?? '';
                    $userClothePattren = strtolower($userClothePattren);

                    $userClotheAstin = $userClotheProcessedImageData?->astin ?? '';
                    $userClotheAstin = strtolower($userClotheAstin);

                    if (($tarh != 'skirtsade' && $tarh != 'wpsade') || $secondClotheTarh != 'sade') {
                        if ($userClothePattren != 'sade') {
                            continue;
                        }
                    }


                    switch ($userBodyType) {
                        case 'women_hourglass':

                            if ($shalvar == 'wbootcut' || $shalvar == 'wbaggy' || $skirtType == 'balloonskirt') {
                                if ($userClotheAstin == 'toppuffy') {
                                    $matched = true;
                                }
                            } elseif ($shalvar == 'wskinny' || $shalvar == 'wstraight' ||
                                $skirtType == 'mermaidskirt' || $skirtType == 'alineskirt' ||
                                $skirtType == 'shortaskirt' || $skirtType == 'pencilskirt' ||
                                $skirtType == 'wrapskirt' || $skirtType == 'miniskirt') {

                                if ($userClotheAstin == 'fsleeveless' || $userClotheAstin == 'fshortsleeve' || $userClotheAstin == 'flongsleeve' ||
                                    $userClotheAstin == 'fhalfsleeve' || $userClotheAstin == 'bottompuffy') {

                                    $matched = true;
                                }

                            } else {
                                $matched = true;
                            }

                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($userClothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'women_rectangle':

                            if ($shalvar == 'wbootcut' || $shalvar == 'wbaggy' || $skirtType == 'balloonskirt') {
                                if ($userClotheAstin == 'toppuffy') {
                                    $matched = true;
                                }
                            } elseif ($shalvar == 'wstraight' || $skirtType == 'alineskirt' || $skirtType == 'shortaskirt' || $skirtType == 'wrapskirt') {

                                if ($userClotheAstin == 'fsleeveless' || $userClotheAstin == 'fshortsleeve' || $userClotheAstin == 'flongsleeve' ||
                                    $userClotheAstin == 'fhalfsleeve' || $userClotheAstin == 'bottompuffy') {

                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($userClothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
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
                                if ($userClotheAstin == 'flongsleeve' || $userClotheAstin == 'bottompuffy' ||
                                    $userClotheAstin == 'fsleeveless' || $userClotheAstin == 'fhalfsleeve') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($userClothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted' || $userClotheColor = 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
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

                                if ($userClotheAstin == 'fshortsleeve' || $userClotheAstin == 'toppuffy') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($userClothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_bright') {
                                if ($userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($userClotheColor == 'light_bright') {
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
                                if ($userClotheAstin == 'flongsleeve' || $userClotheAstin == 'bottompuffy' ||
                                    $userClotheAstin == 'fsleeveless' || $userClotheAstin == 'fhalfsleeve') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($tarh != 'sade' && $tarh != 'skirtsade') {
                                if ($userClothePattren == 'sade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted'
                                        || $userClotheColor = 'light_muted' || $userClotheColor == 'light_bright') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted' || $userClotheColor == 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }


                            }

                            break;
                    }


                } elseif ($currentProcessedImageData->paintane == 'fbalatane') {

                    $userClotheShalvar = $userClotheProcessedImageData?->shalvar ?? '';
                    $userClotheShalvar = strtolower($userClotheShalvar);

                    $userClotheSkirtType = $userClotheProcessedImageData?->skirt_type ?? '';
                    $userClotheSkirtType = strtolower($userClotheSkirtType);

                    $userClotheTarh = $userClotheProcessedImageData?->tarh_shalvar ?? $userClotheProcessedImageData?->skirt_print ?? '';
                    $userClotheTarh = strtolower($userClotheTarh);

                    $pattren = $currentProcessedImageData?->pattren ?? '';
                    $pattren = strtolower($pattren);

                    $astin = $currentProcessedImageData?->astin ?? '';
                    $astin = strtolower($astin);

                    $secondClothePattren = $secondClotheProcessedImageData?->pattren ?? '';
                    $secondClothePattren = strtolower($secondClothePattren);

                    if ($pattren != 'sade' || $secondClothePattren != 'sade') {
                        if ($userClotheTarh != 'skirtsade' || $userClotheTarh != 'wpsade') {
                            continue;
                        }
                    }

                    switch ($userBodyType) {
                        case 'women_hourglass':
                            if ($astin == 'toppuffy') {
                                if ($userClotheShalvar == 'wbootcut' || $userClotheShalvar == 'wbaggy' || $userClotheSkirtType == 'balloonskirt') {
                                    $matched = true;
                                }
                            } elseif ($astin == 'fsleeveless' || $astin == 'fshortsleeve' || $astin == 'flongsleeve' ||
                                $astin == 'fhalfsleeve' || $astin == 'bottompuffy') {

                                if ($userClotheShalvar == 'wskinny' || $userClotheShalvar == 'wstraight' ||
                                    $userClotheSkirtType == 'mermaidskirt' || $userClotheSkirtType == 'alineskirt' ||
                                    $userClotheSkirtType == 'shortaskirt' || $userClotheSkirtType == 'pencilskirt' ||
                                    $userClotheSkirtType == 'wrapskirt' || $userClotheSkirtType == 'miniskirt') {

                                    $matched = true;
                                }

                            } else {
                                $matched = true;
                            }

                            if ($pattren == 'sade') {
                                $matched = true;
                            } else {
                                if ($userClotheTarh == 'sade' || $userClotheTarh == 'skirtsade') {
                                    $matched = true;
                                }
                            }


                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'women_rectangle':

                            if ($astin == 'toppuffy') {
                                if ($userClotheShalvar == 'wbootcut' || $userClotheShalvar == 'wbaggy' || $userClotheSkirtType == 'balloonskirt') {
                                    $matched = true;
                                }
                            } elseif ($astin == 'fsleeveless' || $astin == 'fshortsleeve' || $astin == 'flongsleeve' ||
                                $astin == 'fhalfsleeve' || $astin == 'bottompuffy') {

                                if ($userClotheShalvar == 'wstraight' || $userClotheShalvar == 'wbaggy' ||
                                    $userClotheSkirtType == 'alineskirt' || $userClotheSkirtType == 'shortaskirt' ||
                                    $userClotheSkirtType == 'wrapskirt') {

                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($pattren == 'sade') {
                                $matched = true;
                            } else {
                                if ($userClotheTarh == 'sade' || $userClotheTarh == 'skirtsade') {
                                    $matched = true;
                                }
                            }


                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'women_inverted_triangle':

                            if ($astin == 'fshortsleeve' || $astin == 'toppuffy') {
                                if ($userClotheShalvar == 'wbaggy' || $userClotheShalvar == 'wcargo' ||
                                    $userClotheShalvar == 'wcargoshorts' || $userClotheShalvar == 'wbootcut' ||
                                    $userClotheShalvar == 'wmom' || $userClotheSkirtType == 'balloonskirt' ||
                                    $userClotheSkirtType == 'mermaidskirt') {

                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($pattren == 'sade') {
                                $matched = true;
                            } else {
                                if ($userClotheTarh == 'sade' || $userClotheTarh == 'skirtsade') {
                                    $matched = true;
                                }
                            }

                            if ($color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_bright') {
                                if ($userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($userClotheColor == 'light_bright') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;
                        case 'women_triangle':

                            if ($astin == 'flongsleeve' || $astin == 'bottompuffy' ||
                                $astin == 'fsleeveless' || $astin == 'fhalfsleeve') {
                                if ($userClotheShalvar == 'wskinny' || $userClotheShalvar == 'wstraight' ||
                                    $userClotheShalvar == 'wshorts' || $userClotheSkirtType == 'alineskirt' || $userClotheSkirtType == 'pencilskirt' ||
                                    $userClotheSkirtType == 'shortaskirt') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($pattren == 'sade') {
                                $matched = true;
                            } else {
                                if ($userClotheTarh == 'sade' || $userClotheTarh == 'skirtsade') {
                                    $matched = true;
                                }
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted' || $userClotheColor = 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }


                            }

                            break;
                        case 'women_round':

                            if ($astin == 'fshortsleeve' || $astin == 'toppuffy') {
                                if ($userClotheShalvar == 'wbaggy' || $userClotheShalvar == 'wcargo' ||
                                    $userClotheShalvar == 'wcargoshorts' || $userClotheShalvar == 'wmom' || $userClotheShalvar == 'wshorts' ||
                                    $userClotheSkirtType == 'balloonskirt' || $userClotheSkirtType == 'shortaskirt') {

                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($pattren == 'sade') {
                                $matched = true;
                            } else {
                                if ($userClotheTarh == 'sade' || $userClotheTarh == 'skirtsade') {
                                    $matched = true;
                                }
                            }

                            if ($color == 'dark_bright') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted'
                                    || $userClotheColor = 'light_muted' || $userClotheColor == 'light_bright') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;
                    }
                } elseif ($currentProcessedImageData->paintane == 'mpayintane' && $clotheProcessedImageData->paintane == 'mbalatane') {

                    $shalvar = $currentProcessedImageData?->shalvar ?? '';
                    $shalvar = strtolower($shalvar);

                    $tarh = $currentProcessedImageData?->tarh_shalvar ?? '';
                    $tarh = strtolower($tarh);

                    $userClothePattren = $userClotheProcessedImageData?->pattren ?? '';
                    $userClothePattren = strtolower($userClothePattren);

                    $secondClotheTarh = $secondClotheProcessedImageData?->tarh_shalvar ?? $currentProcessedImageData?->skirt_print ?? '';
                    $secondClotheTarh = strtolower($secondClotheTarh);

                    $userClotheAstin = $userClotheProcessedImageData?->astin ?? '';
                    $userClotheAstin = strtolower($userClotheAstin);

                    if ($tarh != 'mpsade' || $secondClotheTarh != 'sade') {
                        if ($userClothePattren != 'sade') {
                            continue;
                        }
                    }

                    switch ($userBodyType) {
                        case 'men_rectangle':

                            if ($shalvar == 'mcargo' || $shalvar == 'mcargoshorts' || $shalvar == 'mmom') {
                                if ($userClotheAstin == 'shortsleeve') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($tarh != 'mpsade') {
                                if ($userClotheAstin == 'mpsade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_bright') {
                                if ($userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($userClotheColor == 'light_bright') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'men_inverted_triangle':

                            if ($shalvar == 'mslimfit' || $shalvar == 'mshorts') {
                                if ($userClotheAstin == 'longsleeve') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($tarh != 'mpsade') {
                                if ($userClotheAstin == 'mpsade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted' || $userClotheColor = 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }


                            }

                            break;

                        case 'men_oval':

                            if ($shalvar == 'mslimfit' || $shalvar == 'mstraight') {
                                if ($userClotheAstin == 'longsleeve') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($tarh != 'mpsade') {
                                if ($userClotheAstin == 'mpsade') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted'
                                        || $userClotheColor = 'light_muted' || $userClotheColor == 'light_bright') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted' || $userClotheColor == 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }


                            }

                            break;
                    }

                } elseif ($currentProcessedImageData->paintane == 'mbalatane') {

                    $userClotheShalvar = $userClotheProcessedImageData?->shalvar ?? '';
                    $userClotheShalvar = strtolower($userClotheShalvar);

                    $userClotheTarh = $userClotheProcessedImageData?->tarh_shalvar ?? '';
                    $userClotheTarh = strtolower($userClotheTarh);

                    $pattren = $currentProcessedImageData?->pattren ?? '';
                    $pattren = strtolower($pattren);

                    $astin = $currentProcessedImageData?->astin ?? '';
                    $astin = strtolower($astin);

                    $secondClothePattren = $secondClotheProcessedImageData?->pattren ?? '';
                    $secondClothePattren = strtolower($secondClothePattren);

                    if ($pattren != 'sade' || $secondClothePattren != 'sade') {
                        if ($userClotheTarh != 'mpsade') {
                            continue;
                        }
                    }

                    switch ($userBodyType) {
                        case 'men_rectangle':
                            if ($astin == 'longsleeve') {
                                if ($userClotheShalvar == 'mshorts' || $userClotheShalvar == 'mslimfit' || $userClotheShalvar == 'mstraight') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }

                            if ($pattren == 'mpsade') {
                                $matched = true;
                            } else {
                                if ($userClotheTarh == 'mpsade') {
                                    $matched = true;
                                }
                            }


                            if ($color == 'dark_bright' || $color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($color == 'light_bright') {
                                    if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted' || $userClotheColor = 'light_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                } else {
                                    if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted') {
                                        $matched = true;
                                    } else {
                                        $matched = false;
                                    }
                                }
                            }

                            break;

                        case 'men_inverted_triangle':
                            if ($astin == 'shortsleeve') {
                                if ($userClotheShalvar == 'mcargo' || $userClotheShalvar == 'mcargoshorts' || $userClotheShalvar == 'mmom') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($pattren == 'mpsade') {
                                $matched = true;
                            } else {
                                if ($userClotheTarh == 'mpsade') {
                                    $matched = true;
                                }
                            }


                            if ($color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_bright') {
                                if ($userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($userClotheColor == 'light_bright') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;

                        case 'men_oval':
                            if ($astin == 'longsleeve') {
                                if ($userClotheShalvar == 'mshorts' || $userClotheShalvar == 'mslimfit' || $userClotheShalvar == 'mstraight') {
                                    $matched = true;
                                }
                            } elseif ($astin == 'shortsleeve') {
                                if ($userClotheShalvar == 'mcargo' || $userClotheShalvar == 'mcargoshorts' || $userClotheShalvar == 'mmom' ||
                                    $userClotheShalvar == 'mstraight' || $userClotheShalvar == 'mshorts') {
                                    $matched = true;
                                }
                            } else {
                                $matched = true;
                            }


                            if ($pattren == 'mpsade') {
                                $matched = true;
                            } else {
                                if ($userClotheTarh == 'mpsade') {
                                    $matched = true;
                                }
                            }

                            if ($color == 'dark_bright') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'dark_muted') {
                                if ($userClotheColor == 'dark_bright' || $userClotheColor == 'dark_muted'
                                    || $clotheColor = 'light_muted' || $userClotheColor == 'light_bright') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            } elseif ($color == 'light_bright' || $color == 'light_muted') {
                                if ($userClotheColor == 'light_bright' || $userClotheColor == 'light_muted') {
                                    $matched = true;
                                } else {
                                    $matched = false;
                                }
                            }

                            break;
                    }
                }

                #match clothes
                if ($matched) {
                    $userSet = $this->createSet($currentClothes->user_id);
                    $this->attachUserClothesSet($userSet, [$currentClothes->id, $secondClothe->id, $userClothe->id]);
                }

            }

        }

    }

    public function createSet($userId): UserSet
    {
        return (new UserSetRepository())->create(["user_id" => $userId]);
    }

    public function attachUserClothesSet(UserSet $userSet, array $clotheIds): void
    {
        $userSet->clothes()->attach($clotheIds);
    }
}
