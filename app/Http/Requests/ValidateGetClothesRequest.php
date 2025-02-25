<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateGetClothesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clothes_type'=>'nullable|integer|min:1|max:3',
            'process_status'=>'nullable|integer|min:1|max:2',
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
