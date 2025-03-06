<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class ValidateRegisterOrder extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'shop_id' => 'required|integer|exists:shops,id,deleted_at,NULL',
            'user_address_id' => 'required|integer|exists:user_addresses,id',
            'delivery_type' => 'required|string|in:store,shipping',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.selected_size' => 'required|string',
            'items.*.count' => 'required|integer|min:1',
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
