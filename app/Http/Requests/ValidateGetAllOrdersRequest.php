<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidateGetAllOrdersRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'shop_id' => 'nullable|integer|exists:shops,id,deleted_at,NULL',
            'user_id' => 'nullable|integer|exists:users,id,deleted_at,NULL',
            'user_address_id' => 'nullable|integer|exists:user_addresses,id,deleted_at,NULL',
            'delivery_type' => 'nullable|string|in:store,shipping',
            'tracking_number' => 'nullable|string',
            'status' => 'nullable|string|in:inProgress,delivered,returned,canceled',
            'progress_status' => 'nullable|string|in:pendingForPayment,waitingForConform,waitingForPacking,readyForDelivery,waitingForConfirmReturning,waitingForProcessReturning,delivered,returned,canceled,canceledSystemically',
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
