<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateGetAllWalletsRequest;
use App\Services\WalletsService;
use Exception;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    public function __construct(public WalletsService $service)
    {
    }

    /**
     * @param ValidateGetAllWalletsRequest $request
     * @return JsonResponse
     */
    public function index(ValidateGetAllWalletsRequest $request): JsonResponse
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
