<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCreateWalletTransaction;
use App\Http\Requests\ValidateGetAllWalletTransactionsRequest;
use App\Http\Requests\ValidateUpdateWalletTransaction;
use App\Services\WalletTransactionsService;
use Exception;
use Illuminate\Http\JsonResponse;

class WalletTransactionController extends Controller
{
    public function __construct(public WalletTransactionsService $service)
    {
    }

    /**
     * @param ValidateGetAllWalletTransactionsRequest $request
     * @return JsonResponse
     */
    public function index(ValidateGetAllWalletTransactionsRequest $request): JsonResponse
    {
        $inputs = $request->validated();
        $result = $this->service->index($inputs);
        return ResponseHelper::responseSuccess($result);
    }


    /**
     * @param int $walletTransactionId
     * @return JsonResponse
     */
    public function show(int $walletTransactionId): JsonResponse
    {
        try {
            $data = $this->service->show($walletTransactionId);
            return ResponseHelper::responseSuccess($data);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }

    /**
     * @param ValidateCreateWalletTransaction $request
     * @return JsonResponse
     */
    public function create(ValidateCreateWalletTransaction $request): JsonResponse
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
     * @param ValidateUpdateWalletTransaction $request
     * @param int $walletTransactionId
     * @return JsonResponse
     */
    public function update(ValidateUpdateWalletTransaction $request, int $walletTransactionId): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $data = $this->service->update($inputs, $walletTransactionId);
            return ResponseHelper::responseSuccess($data);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }

    }

    /**
     * @param int $walletTransactionId
     * @return JsonResponse
     */
    public function delete(int $walletTransactionId): JsonResponse
    {
        try {
            $this->service->delete($walletTransactionId);
            $message = __("custom.defaults.delete_success");
            return ResponseHelper::responseSuccess([], $message);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }

}
