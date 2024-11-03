<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
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
            return ResponseHelper::responseSuccess([],$message);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }



}
