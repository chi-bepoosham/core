<?php

namespace App\Services;

use App\Http\Repositories\BodyTypeRepository;
use Exception;
use Illuminate\Support\Collection;

class BodyTypesService
{

    public function __construct(public BodyTypeRepository $repository)
    {
    }


    /**
     * @return Collection
     */
    public function index(): Collection
    {
        $gender = request('gender');
        if ($gender != null) {
            return $this->repository->all(["gender" => $gender]);
        }

        return $this->repository->all();
    }
}
