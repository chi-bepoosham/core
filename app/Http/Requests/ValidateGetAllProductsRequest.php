<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateGetAllProductsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'nullable|string',
            'shop_id' => 'nullable|integer|exists:shops,id',
            'category_id' => 'nullable|integer|exists:product_categories,id',
            'main_id' => 'nullable|integer|exists:products,id',
            'gender' => 'nullable|string|in:male,female,unisex',
            'is_available' => 'nullable|integer|min:0|max:1',
            'sizes' => 'nullable|array',
            'from_price' => 'nullable|integer|min:1',
            'to_price' => 'nullable|required_with:from_price|integer',
            'colors' => 'nullable|array',
            'colors.*' => ['nullable', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'paginate' => 'nullable|boolean',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1',
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
