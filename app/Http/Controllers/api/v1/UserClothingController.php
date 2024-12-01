<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateGetClothesRequest;
use App\Http\Requests\ValidateUploadImage;
use App\Http\Requests\ValidateUpdateUser;
use App\Services\UserClothesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\UsersService;

class UserClothingController extends Controller
{
    public function __construct(public UserClothesService $service)
    {
    }

    public function index(ValidateGetClothesRequest $request): JsonResponse
    {
        $inputs = $request->validated();
        $result = $this->service->index($inputs);
        return ResponseHelper::responseSuccess($result);
    }


    public function uploadClothingImage(ValidateUploadImage $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $result = $this->service->uploadClothingImage($inputs);
            $message = __("custom.defaults.upload_success");
            return ResponseHelper::responseSuccess($result, $message);
        } catch (\Exception $exception) {
            $message = __("custom.defaults.upload_failed");
            return ResponseHelper::responseCustomError($message);
        }
    }

    /**
     * @param $clothesId
     * @return JsonResponse
     */
    public function delete($clothesId): JsonResponse
    {
        try {
            $this->service->delete($clothesId);
            $message = __("custom.defaults.delete_success");
            return ResponseHelper::responseSuccess([],$message);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }

}
