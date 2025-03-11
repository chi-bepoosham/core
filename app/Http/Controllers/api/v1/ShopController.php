<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateGetAllShopsRequest;
use App\Http\Requests\ValidateUpdateShop;
use App\Services\ShopsService;
use Exception;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    public function __construct(public ShopsService $service)
    {
    }



    /**
     * @return JsonResponse
     */
    public function splash(): JsonResponse
    {
        try {
            $result = $this->service->splash();
            return ResponseHelper::responseSuccess($result);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $code = $exception->getCode();
            return ResponseHelper::responseCustomError($message, $code);
        }
    }


    /**
     * @param ValidateGetAllShopsRequest $request
     * @return JsonResponse
     */
    public function index(ValidateGetAllShopsRequest $request): JsonResponse
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
     * @param ValidateUpdateShop $request
     * @param int $shopId
     * @return JsonResponse
     */
    public function update(ValidateUpdateShop $request, int $shopId): JsonResponse
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
