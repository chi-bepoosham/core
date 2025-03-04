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
     * @param ValidateGetAllProductsRequest $request
     * @return JsonResponse
     */
    public function indexUsers(ValidateGetAllProductsRequest $request): JsonResponse
    {
        $inputs = $request->validated();
        $relations = ['category', 'images'];
        $result = $this->service->index($inputs, $relations);
        return ResponseHelper::responseSuccess($result);
    }


    /**
     * @param int $productId
     * @return JsonResponse
     */
    public function show(int $productId): JsonResponse
    {
        try {
            $data = $this->service->show($productId);
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
     * @param int $productId
     * @return JsonResponse
     */
    public function update(ValidateUpdateProduct $request, int $productId): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $data = $this->service->update($inputs, $productId);
            return ResponseHelper::responseSuccess($data);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }

    }

    /**
     * @param int $productId
     * @return JsonResponse
     */
    public function delete(int $productId): JsonResponse
    {
        try {
            $this->service->delete($productId);
            $message = __("custom.defaults.delete_success");
            return ResponseHelper::responseSuccess([], $message);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }

}
