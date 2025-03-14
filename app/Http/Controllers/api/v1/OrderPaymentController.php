<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\OrderPaymentsService;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderPaymentController extends Controller
{
    public function __construct(public OrderPaymentsService $service)
    {
    }


    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function verifyPayment(Request $request): Response|JsonResponse
    {
        try {
            $inputs = $request->all();
            $data = $this->service->verifyPayment($inputs);
            $returnUrl = 'https://chibepoosham.app/payment/status';
            return Http::post($returnUrl, $data);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }

    }


}
