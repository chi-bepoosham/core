<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCreateProduct;
use App\Http\Requests\ValidateGetAllProductsRequest;
use App\Http\Requests\ValidateGetAllShopsRequest;
use App\Http\Requests\ValidateUpdateProduct;
use App\Http\Requests\ValidateUpdateShop;
use App\Services\ProductsService;
use Exception;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(public ProductsService $service)
    {
    }

    /**
     * @param ValidateGetAllProductsRequest $request
     * @return JsonResponse
     */
    public function index(ValidateGetAllProductsRequest $request): JsonResponse
    {
        $inputs = $request->validated();
        $result = $this->service->index($inputs);
        return ResponseHelper::responseSuccess($result);
    }


    /**
     * @param int $shopId
     * @return JsonResponse
     */
    public function show(int $shopId): JsonResponse
    {
        try {
            $data = $this->service->show($shopId);
            return ResponseHelper::responseSuccess($data);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }


    /**
     * @param ValidateCreateProduct $request
     * @return JsonResponse
     */
    public function create(ValidateCreateProduct $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $data = $this->service->create($inputs);
            return ResponseHelper::responseSuccess($data);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }

    }


    /**
     * @param ValidateUpdateProduct $request
     * @param int $shopId
     * @return JsonResponse
     */
    public function update(ValidateUpdateProduct $request, int $shopId): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $data = $this->service->update($inputs, $shopId);
            return ResponseHelper::responseSuccess($data);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }

    }

    /**
     * @param int $shopId
     * @return JsonResponse
     */
    public function delete(int $shopId): JsonResponse
    {
        try {
            $this->service->delete($shopId);
            $message = __("custom.defaults.delete_success");
            return ResponseHelper::responseSuccess([], $message);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }

}
