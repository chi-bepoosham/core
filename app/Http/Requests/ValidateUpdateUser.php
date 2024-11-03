<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class ValidateUpdateUser extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'mobile' => 'required|bail|regex:/^(09){1}[0-9]{9}+$/',
            'birthday' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email',
            'gender' => 'nullable|integer|min:1|max:3',
            'avatar' => 'nullable|file|mimes:jpg,jpeg,png,wep,webp',
            'delete_avatar' => 'nullable|boolean',
        ];
    }


    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
