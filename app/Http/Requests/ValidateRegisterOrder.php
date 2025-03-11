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
        $rules = [
            'shop_id' => 'required|integer|exists:shops,id,deleted_at,NULL',
            'user_address_id' => 'required|integer|exists:user_addresses,id,deleted_at,NULL',
            'delivery_type' => 'required|string|in:store,shipping',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id,deleted_at,NULL',
            'items.*.selected_size' => 'required|string',
            'items.*.count' => 'required|integer|min:1',
        ];

        if (isset(request()->userAdmin)) {
            return $rules +
                [
                    'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
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

}
