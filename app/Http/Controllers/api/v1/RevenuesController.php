<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCreateRevenues;
use App\Http\Requests\ValidateCreateWalletTransaction;
use App\Http\Requests\ValidateGetAllRevenuesRequest;
use App\Services\RevenuesService;
use Exception;
use Illuminate\Http\JsonResponse;

class RevenuesController extends Controller
{
    public function __construct(public RevenuesService $service)
    {
    }

    /**
     * @param ValidateGetAllRevenuesRequest $request
     * @return JsonResponse
     */
    public function index(ValidateGetAllRevenuesRequest $request): JsonResponse
    {
        $inputs = $request->validated();
        $result = $this->service->index($inputs);
        return ResponseHelper::responseSuccess($result);
    }

    /**
     * @param int $revenuesId
     * @return JsonResponse
     */
    public function show(int $revenuesId): JsonResponse
    {
        try {
            $data = $this->service->show($revenuesId);
            return ResponseHelper::responseSuccess($data);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }


    /**
     * @param ValidateCreateRevenues $request
     * @return JsonResponse
     */
    public function create(ValidateCreateRevenues $request): JsonResponse
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
     * @param int $revenuesId
     * @return JsonResponse
     */
    public function delete(int $revenuesId): JsonResponse
    {
        try {
            $this->service->delete($revenuesId);
            $message = __("custom.defaults.delete_success");
            return ResponseHelper::responseSuccess([], $message);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }

}
