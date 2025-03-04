<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateUserAddress;
use App\Services\UserAddressesService;
use Exception;
use Illuminate\Http\JsonResponse;

class UserAddressController extends Controller
{
    public function __construct(public UserAddressesService $service)
    {
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result = $this->service->index();
        return ResponseHelper::responseSuccess($result);
    }



    /**
     * @param int $addressId
     * @return JsonResponse
     */
    public function show(int $addressId): JsonResponse
    {
        try {
            $data = $this->service->show($addressId);
            return ResponseHelper::responseSuccess($data);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }


    /**
     * @param ValidateUserAddress $request
     * @return JsonResponse
     */
    public function create(ValidateUserAddress $request): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $data = $this->service->create($inputs);
            return ResponseHelper::responseSuccess($data);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }

    }


    /**
     * @param ValidateUserAddress $request
     * @param int $addressId
     * @return JsonResponse
     */
    public function update(ValidateUserAddress $request, int $addressId): JsonResponse
    {
        $inputs = $request->validated();
        try {
            $data = $this->service->update($inputs, $addressId);
            return ResponseHelper::responseSuccess($data);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }

    }

    /**
     * @param int $addressId
     * @return JsonResponse
     */
    public function delete(int $addressId): JsonResponse
    {
        try {
            $this->service->delete($addressId);
            $message = __("custom.defaults.delete_success");
            return ResponseHelper::responseSuccess([], $message);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }
    }

}
