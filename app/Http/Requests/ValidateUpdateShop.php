<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class ValidateUpdateShop extends FormRequest
{

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->setValidatorNationalCode();
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }


    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string',
            'province_id' => 'required|integer|exists:provinces,id',
            'city_id' => 'required|integer|exists:cities,id',
            'address' => 'required|string',
            'location_lat' => 'nullable|string|regex:/^[-]?[\d]+[.][\d]*$/|min:6|max:20',
            'location_lng' => 'nullable|string|regex:/^[-]?[\d]+[.][\d]*$/|min:6|max:20',
            'manager_name' => 'required|string',
            'manager_national_code' => 'required|national_code|min:8|max:12',
            'mobile' => 'required|regex:/^(09){1}[0-9]{9}+$/|unique:shops,mobile,' . $this->route('shopId'),
            'brand_name' => 'nullable|string',
            'description' => 'nullable|string',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,wep,webp',
            'phone' => 'nullable|string|regex:/(0)[0-9]{10}/|size:11',
            'email' => 'nullable|email|unique:shops,email,' . $this->route('shopId'),
            'web_site' => 'nullable|url',
            'password' => 'nullable|string',
        ];

        if (isset(request()->userAdmin)) {
            return $rules +
                [
                    'main_id' => 'nullable|integer|exists:shops,id',
                    'is_active' => 'nullable|integer|min:0|max:1',
                    'is_verified' => 'nullable|integer|min:0|max:1',
                ];
        }

        return $rules;
    }


    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    private function setValidatorNationalCode(): void
    {
        # national_code
        Validator::extend('national_code', function ($attribute, $national_code, $parameters) {

            if (!preg_match('/^\d{8,10}$/', $national_code) || preg_match('/^[0]{10}|[1]{10}|[2]{10}|[3]{10}|[4]{10}|[5]{10}|[6]{10}|[7]{10}|[8]{10}|[9]{10}$/', $national_code)) {
                return false;
            }
            $sub = 0;
            if (strlen($national_code) == 8) {
                $national_code = '00' . $national_code;
            } elseif (strlen($national_code) == 9) {
                $national_code = '0' . $national_code;
            }

            for ($i = 0; $i <= 8; $i++) {
                $sub = $sub + ($national_code[$i] * (10 - $i));
            }

            if (($sub % 11) < 2) {
                $control = ($sub % 11);
            } else {
                $control = 11 - ($sub % 11);
            }
            if ($national_code[9] == $control) {
                return true;
            } else {
                return false;
            }
        }, __('custom.validation.national_code'));
    }

}
