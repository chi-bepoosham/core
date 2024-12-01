<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ValidateOtpConfirmAuth;
use App\Http\Requests\ValidateRegisterUser;
use App\Http\Requests\ValidateSendOtpAuth;
use App\Services\AuthenticationsService;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(public AuthenticationsService $service)
    {
    }


    /**
     * @param ValidateSendOtpAuth $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function sendOtp(ValidateSendOtpAuth $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $result = $this->service->sendOtp($inputs);
            $message = __("custom.user.send_otp_successfully");
            return ResponseHelper::responseSuccess([], $message);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }



    /**
     * @param ValidateOtpConfirmAuth $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function otpConfirm(ValidateOtpConfirmAuth $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $result = $this->service->otpConfirm($inputs);
            return ResponseHelper::responseSuccess($result);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }



    /**
     * @param ValidateRegisterUser $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function register(ValidateRegisterUser $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $result = $this->service->register($inputs);
            return ResponseHelper::responseSuccess($result);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }

}
