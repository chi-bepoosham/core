<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ValidateUpdateWalletTransaction extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'order_id' => 'nullable|integer|exists:orders,id,deleted_at,NULL',
            'type' => 'required|string|in:order,cancel_order,return_order,ads,withdraw',
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
