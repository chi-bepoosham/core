<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateGetAllShopsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'=>'nullable|string',
            'manager_national_code'=>'nullable|string',
            'mobile'=>'nullable|string',
            'is_active'=>'nullable|integer|min:0|max:1',
            'is_verified'=>'nullable|integer|min:0|max:1',
            'paginate'=>'nullable|boolean',
            'page'=>'nullable|integer|min:1',
            'per_page'=>'nullable|integer|min:1',
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
