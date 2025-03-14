<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\OrderPaymentsService;
use Exception;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class OrderPaymentController extends Controller
{
    public function __construct(public OrderPaymentsService $service)
    {
    }


    /**
     * @param Request $request
     * @return Application|RedirectResponse|Redirector|JsonResponse
     */
    public function verifyPayment(Request $request): Application|RedirectResponse|Redirector|JsonResponse
    {
        try {
            $inputs = $request->all();
            $params = $this->service->verifyPayment($inputs);
            $queryString = http_build_query($params);
            $returnUrl = 'https://chibepoosham.app/payment/status';
            return redirect()->away($returnUrl . '?' . $queryString);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }

    }


}
