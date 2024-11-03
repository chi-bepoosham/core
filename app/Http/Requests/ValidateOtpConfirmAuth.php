<?php

namespace App\Http\Requests;

use App\Helpers\Response\ResponseHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class ValidateOtpConfirmAuth extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->set_validator_otp_check_expired_time();
        $this->set_validator_otp_confirm();
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mobile' => 'required|regex:/^(09){1}[0-9]{9}+$/',
            'code' => 'required|bail|integer|otp_check_expired_time|otp_confirm',
        ];
    }


    private function helper_otp_confirm($attribute, $code_value, $parameters): bool
    {
        $mobile = request()->get('mobile');
        if (cache()->has($mobile)) {
            $cache_information = cache()->get($mobile);
            $code = $cache_information['code'] ?? null;
            return $code_value == $code;
        }
        return false;
    }


    private function helper_otp_check_expired_time($attribute, $code_value, $parameters): bool
    {
        $mobile = request()->get('mobile');
        if (cache()->has($mobile)) {
            $cache_information = cache()->get($mobile);
            $expired_time = $cache_information['expired_time'] ?? null;
            return Carbon::now() <= Carbon::parse($expired_time);
        }
        return false;
    }

    private function set_validator_otp_confirm()
    {
        Validator::extend('otp_confirm', function ($attribute, $code_value, $parameters) {
            return $this->helper_otp_confirm(attribute: $attribute, code_value: $code_value, parameters: $parameters);
        }, 'کد وارد شده با کد ارسالی مطابقت ندارد');
    }

    private function set_validator_otp_check_expired_time()
    {
        Validator::extend('otp_check_expired_time', function ($attribute, $code_value, $parameters){
            return $this->helper_otp_check_expired_time($attribute, $code_value, $parameters);
        }, 'زمان کد منقضی شده است . لطفا دوباره درخواست بدهید');
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
