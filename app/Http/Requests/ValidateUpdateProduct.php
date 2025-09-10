<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class ValidateUpdateProduct extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'category_id' => 'required|integer|exists:product_categories,id,deleted_at,NULL',
            'main_id' => 'nullable|integer|exists:products,id,deleted_at,NULL',
            'color' => ['nullable', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'gender' => 'required|string|in:male,female,unisex',
            'sizes' => 'nullable|array',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:1000',
            'is_available' => 'required|integer|min:0|max:1',
            'deleted_image_ids' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*.file' => 'required|file|mimes:jpg,jpeg,png,wep,webp,heic',
            'images.*.is_selected' => 'required|integer|min:0|max:1',
            'images.*.is_processed' => 'required|integer|min:0|max:1',
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
