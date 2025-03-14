<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ValidateCreateRevenues extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'wallet_transaction_id' => 'nullable|required_without:shop_id|integer|exists:wallet_transactions,id,deleted_at,NULL',
            'type' => 'required|string|in:order,ads',
            'amount' => 'required|integer|min:1000',
            'date_time' => 'nullable|date_format:Y-m-d H:i:s',
            'description' => 'nullable|string',
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
