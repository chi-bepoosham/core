<?php

namespace App\Http\Repositories;

use App\Models\BodyType;

class BodyTypeRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return BodyType::class;
    }


    /**
     * @return string[]
     */
    public function relations(): array
    {
        return ["celebrities", "clothes"];
    }
}
