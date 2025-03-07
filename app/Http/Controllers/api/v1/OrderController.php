<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCreateProduct;
use App\Http\Requests\ValidateGetAllOrdersRequest;
use App\Http\Requests\ValidateGetAllProductsRequest;
use App\Http\Requests\ValidateGetAllShopsRequest;
use App\Http\Requests\ValidateRegisterOrder;
use App\Http\Requests\ValidateSearchAllRequest;
use App\Http\Requests\ValidateUpdateOrder;
use App\Http\Requests\ValidateUpdateProduct;
use App\Http\Requests\ValidateUpdateShop;
use App\Services\OrdersService;
use App\Services\ProductsService;
use Exception;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(public OrdersService $service)
    {
    }

    /**
     * @param ValidateGetAllOrdersRequest $request
     * @return JsonResponse
     */
    public function index(ValidateGetAllOrdersRequest $request): JsonResponse
    {
        $inputs = $request->validated();
        $result = $this->service->index($inputs);
        return ResponseHelper::responseSuccess($result);
    }


    /**
     * @param int $orderId
     * @return JsonResponse
     */
    public function show(int $orderId): JsonResponse
    {
        try {
            $data = $this->service->show($orderId);
            return ResponseHelper::responseSuccess($data);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }


    /**
     * @param ValidateRegisterOrder $request
     * @return JsonResponse
     */
    public function register(ValidateRegisterOrder $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $data = $this->service->register($inputs);
            return ResponseHelper::responseSuccess($data);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }

    }


    /**
     * @param ValidateUpdateOrder $request
     * @param int $orderId
     * @return JsonResponse
     */
    public function update(ValidateUpdateOrder $request, int $orderId): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $data = $this->service->update($inputs, $orderId);
            return ResponseHelper::responseSuccess($data);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }

    }

    /**
     * @param int $orderId
     * @return JsonResponse
     */
    public function delete(int $orderId): JsonResponse
    {
        try {
            $this->service->delete($orderId);
            $message = __("custom.defaults.delete_success");
            return ResponseHelper::responseSuccess([], $message);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }

}
