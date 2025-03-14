<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class ValidateUpdateOrder extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        if (isset(request()->userShop)) {
            return [
                'status' => 'nullable|string|in:inProgress,delivered,returned,canceled',
                'progress_status' => 'nullable|string|in:waitingForConfirm,waitingForPacking,readyForDelivery,waitingForConfirmReturning,waitingForProcessReturning,delivered,returned,canceled',
            ];
        }elseif (isset(request()->userAdmin)) {
            return [
                'user_address_id' => 'nullable|integer|exists:user_addresses,id,deleted_at,NULL',
                'delivery_type' => 'nullable|string|in:store,shipping',
                'description' => 'nullable|string',
                'status' => 'nullable|string|in:inProgress,delivered,returned,canceled',
                'progress_status' => 'nullable|string|in:pendingForPayment,waitingForConfirm,waitingForPacking,readyForDelivery,waitingForConfirmReturning,waitingForProcessReturning,delivered,returned,canceled,canceledSystemically',
            ];
        }else{
            return [
                'user_address_id' => 'nullable|integer|exists:user_addresses,id,deleted_at,NULL',
                'delivery_type' => 'nullable|string|in:store,shipping',
                'description' => 'nullable|string',
                'status' => 'nullable|string|in:delivered,returned,canceled',
            ];
        }
    }


    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
