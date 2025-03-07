<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\Response\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCreateProduct;
use App\Http\Requests\ValidateGetAllProductsRequest;
use App\Services\UserMarkedProductsService;
use Exception;
use Illuminate\Http\JsonResponse;

class UserMarkedProductController extends Controller
{
    public function __construct(public UserMarkedProductsService $service)
    {
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $inputs = [];
        $result = $this->service->index($inputs);
        return ResponseHelper::responseSuccess($result);
    }


    /**
     * @param int $productId
     * @param  $markedStatus
     * @return JsonResponse
     */
    public function changeMarkedStatus(int $productId, $markedStatus): JsonResponse
    {
        try {
            $data = $this->service->changeMarkedStatus($productId, $markedStatus);

            if ($data == 'marked'){
                $message = __("custom.shop.product_marked");
            }else{
                $message = __("custom.shop.product_unmarked");
            }

            return ResponseHelper::responseSuccess([],$message);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            return ResponseHelper::responseCustomError($message);
        }

    }

}
