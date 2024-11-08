<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateUploadImage;
use App\Http\Requests\ValidateUpdateUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\UsersService;

class UserController extends Controller
{
    public function __construct(public UsersService $service)
    {
    }


    public function updateUser(ValidateUpdateUser $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $this->service->updateUser($inputs);
            $message = __("custom.defaults.update_success");
            return ResponseHelper::responseSuccess([], $message);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }


    public function uploadBodyImage(ValidateUploadImage $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $this->service->updateBodyImage($inputs);
            $message = __("custom.defaults.upload_success");
            return ResponseHelper::responseSuccess([], $message);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }

    public function getBodyTypeDetail(): JsonResponse
    {
        try {
            $result = $this->service->getBodyTypeDetail();
            return ResponseHelper::responseSuccess($result);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $code = $exception->getCode();
            return ResponseHelper::responseCustomError($message, $code);
        }
    }


}
