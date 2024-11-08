<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateUploadImage;
use App\Http\Requests\ValidateUpdateUser;
use App\Services\BodyTypesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BodyTypeController extends Controller
{
    public function __construct(public BodyTypesService $service)
    {
    }


    public function index(): JsonResponse
    {
        $result = $this->service->index();
        return ResponseHelper::responseSuccess($result);
    }





}
