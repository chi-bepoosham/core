<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateLoginAdmin;
use App\Http\Requests\ValidateLoginShop;
use App\Http\Requests\ValidateRegisterShop;
use Exception;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\ValidateOtpConfirmAuth;
use App\Http\Requests\ValidateRegisterUser;
use App\Http\Requests\ValidateSendOtpAuth;
use App\Services\AuthenticationsService;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Laravel\Socialite\Facades\Socialite;
use Psr\SimpleCache\InvalidArgumentException;

class AuthController extends Controller
{
    public function __construct(public AuthenticationsService $service)
    {
    }


    /**
     * @param ValidateSendOtpAuth $request
     * @return JsonResponse
     * @throws Exception|InvalidArgumentException
     */
    public function sendOtp(ValidateSendOtpAuth $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $this->service->sendOtp($inputs);
            $message = __("custom.user.send_otp_successfully");
            return ResponseHelper::responseSuccess([], $message);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }


    /**
     * @param ValidateOtpConfirmAuth $request
     * @return JsonResponse
     * @throws Exception|InvalidArgumentException
     */
    public function otpConfirm(ValidateOtpConfirmAuth $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $result = $this->service->otpConfirm($inputs);
            return ResponseHelper::responseSuccess($result);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }


    /**
     * @param ValidateRegisterUser $request
     * @return JsonResponse
     * @throws Exception
     */
    public function register(ValidateRegisterUser $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $result = $this->service->register($inputs);
            return ResponseHelper::responseSuccess($result);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }


    /**
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogleOAuth(): RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function callbackGoogleOAuth(): Application|RedirectResponse|Redirector|Response
    {
        try {
            $token = $this->service->callbackGoogleOAuth();
            return redirect(env("GOOGLE_CLIENT_APP_URL") . "?token=$token");
        } catch (Exception $exception) {
            return response()->view('errors.custom', [
                'code' => 403,
                'message' => $exception->getMessage()
            ]);
        }
    }


    /**
     * @param ValidateLoginShop $request
     * @return JsonResponse
     */
    public function shopLogin(ValidateLoginShop $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $result = $this->service->shopLogin($inputs);
            return ResponseHelper::responseSuccess($result);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }

    /**
     * @param ValidateRegisterShop $request
     * @return JsonResponse
     */
    public function shopRegister(ValidateRegisterShop $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $result = $this->service->shopRegister($inputs);
            return ResponseHelper::responseSuccess($result);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }



    /**
     * @param ValidateLoginAdmin $request
     * @return JsonResponse
     */
    public function adminLogin(ValidateLoginAdmin $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $result = $this->service->adminLogin($inputs);
            return ResponseHelper::responseSuccess($result);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }


    /**
     * @return JsonResponse
     */
    public function userLogout(): JsonResponse
    {
        $this->service->userLogout();
        $message = __("custom.user.logout_successfully");
        return ResponseHelper::responseSuccess([], $message);
    }


}
