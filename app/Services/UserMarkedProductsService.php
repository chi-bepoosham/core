<?php

namespace App\Services;

use App\Http\Repositories\ProductRepository;
use App\Http\Repositories\ShopRepository;
use App\Http\Repositories\UserMarkedProductRepository;
use App\Models\ProductImage;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Throwable;
use function PHPUnit\Framework\isEmpty;

class UserMarkedProductsService
{

    public function __construct(public UserMarkedProductRepository $repository)
    {
    }

    /**
     * @param array $inputs
     * @return Collection|LengthAwarePaginator
     */
    public function index(array $inputs = []): Collection|LengthAwarePaginator
    {
        $relations = ['product'];
        $data =  $this->repository->resolve_paginate(inputs: $inputs, relations: $relations, orderByColumn: 'shop_id');

        if ($data instanceof LengthAwarePaginator) {
            $products = $data->values()->pluck('product');
            $data = $data->toArray();
            $data['data'] = $products;
            $data = collect($data);
        }else{
            $data = $data->pluck('product')->values();
        }

        return $data;
    }


    /**
     * @param int $productId
     * @param $markedStatus
     * @return string
     * @throws Exception
     */
    public function changeMarkedStatus(int $productId, $markedStatus): string
    {
        $product = (new ProductRepository())->findWithRelations($productId);
        if ($product == null) {
            throw new Exception(__("custom.shop.product_not_exist"));
        }

        $markedStatus = strtolower($markedStatus);
        if ($markedStatus == 'true' || $markedStatus == 1) {
            $markedStatus = true;
        } else {
            $markedStatus = false;
        }

        DB::beginTransaction();
        try {

            if ($markedStatus) {
                $item = $this->repository->findBy('product_id', $productId);
                if ($item == null) {
                    $inputs = [
                        'user_id' => Auth::id(),
                        'product_id' => $productId,
                        'shop_id' => $product->shop_id,
                    ];
                    $this->repository->create($inputs);
                    DB::commit();
                }


                return 'marked';

            } else {
                $item = $this->repository->findBy('product_id', $productId);
                if ($item != null) {
                    $this->repository->delete($item);
                    DB::commit();
                }

                return 'unmarked';
            }

        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.store_failed"));
        }
    }
}
