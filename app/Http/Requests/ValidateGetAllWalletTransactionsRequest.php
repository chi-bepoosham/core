<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ValidateGetAllWalletTransactionsRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        if (isset(request()->userShop)) {
            $this->merge([
                'shop_id' => Auth::id(),
                'wallet_id' => null
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'shop_id' => 'nullable|integer|exists:shops,id,deleted_at,NULL',
            'wallet_id' => 'nullable|integer|exists:wallets,id,deleted_at,NULL',
            'type' => 'nullable|string|in:order,cancel_order,return_order,ads,withdraw',
            'order_id' => 'nullable|integer|exists:orders,id,deleted_at,NULL',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|required_with:from_date|date',
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
