<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ValidateSendOtpAuth extends FormRequest
{
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->setValidatorCheckAllowSendCode();
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mobile' => 'required|check_allow_send_code|regex:/^(09){1}[0-9]{9}+$/',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    private function setValidatorCheckAllowSendCode(): void
    {
        # check_allow_send_code
        Validator::extend('check_allow_send_code', function ($attribute, $mobile, $parameters) {
            $cache_information = cache()->get($mobile) ?? null;
            if ($cache_information) {
                $expired_time = $cache_information['expired_time'] ?? null;
                return $expired_time && now() > Carbon::make($expired_time);
            }
            return true;
        }, __('custom.user.otp_check_allow_send_code'));
    }
}
