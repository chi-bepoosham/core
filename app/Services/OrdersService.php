<?php

namespace App\Services;

use App\Http\Repositories\OrderRepository;
use App\Http\Repositories\ProductRepository;
use App\Http\Repositories\ShopRepository;
use App\Http\Repositories\UserAddressRepository;
use App\Models\OrderItem;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdersService
{

    public function __construct(public OrderRepository $repository)
    {
    }

    /**
     * @param $inputs
     * @param array $relations
     * @return Collection|LengthAwarePaginator
     */
    public function index($inputs, array $relations = []): Collection|LengthAwarePaginator
    {
        $inputs["user_id"] = Auth::id();
        return $this->repository->resolve_paginate(inputs: $inputs, relations: $relations);
    }


    /**
     * @param $orderId
     * @return object|null
     * @throws Exception
     */
    public function show($orderId): ?object
    {
        $item = $this->repository->findWithRelations($orderId);
        if ($item == null) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        if ($item->user_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }

        return $item;
    }

    /**
     * @param $inputs
     * @return mixed
     * @throws Exception
     */
    public function register($inputs): mixed
    {
        $inputs["user_id"] = Auth::id();

        $shop = (new ShopRepository())->find($inputs["shop_id"]);

        $userAddress = (new UserAddressRepository())->find($inputs["user_address_id"]);
        if ($userAddress == null || $userAddress->user_id != Auth::id()) {
            throw new Exception(__("custom.shop.not_access_register_order_user_address"));
        }
        $inputs["tracking_number"] = $this->generateTrackingCode();
        $items = $this->prepareOrderItems($inputs["items"], $shop->id);
        unset($inputs["items"]);

        $inputs["total_price"] = array_sum(array_column($items, 'total_price'));
        $inputs["shipping_fee"] = $shop->shipping_fee;
        $inputs["final_price"] = $inputs["total_price"] + $inputs["shipping_fee"];

        DB::beginTransaction();
        try {
            $createdItem = $this->repository->create($inputs);

            $createdItem->items()->saveMany($items);

            DB::commit();

            return $this->show($createdItem->id);
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.store_failed"));
        }
    }

    /**
     * @param $inputs
     * @param $orderId
     * @return mixed
     * @throws Exception
     */
    public function update($inputs, $orderId): mixed
    {
        $item = $this->repository->find($orderId);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        if ($item->user_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }


        DB::beginTransaction();
        try {
            $this->repository->update($item, $inputs);
            $order = $this->repository->findWithRelations($orderId);
            DB::commit();
            return $order;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.update_failed"));
        }
    }

    /**
     * @param $orderId
     * @return bool
     * @throws Exception
     */
    public function delete($orderId): bool
    {
        $item = $this->repository->find($orderId);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        if ($item->user_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }

        DB::beginTransaction();
        try {
            $this->repository->delete($item);
            DB::commit();
            return true;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.delete_failed"));
        }
    }


    /**
     * @param $items
     * @param $shopId
     * @return array
     * @throws Exception
     */
    public function prepareOrderItems($items, $shopId): array
    {
        $productRepository = new ProductRepository();
        $allItems = [];
        foreach ($items as $item) {
            $product = $productRepository->findWithInputs(["id" => $item['product_id'], "shop_id" => $shopId], withTrashed: true);
            if ($product == null || $product->deleted_at != null) {
                throw new Exception(__("custom.shop.not_access_register_order_product_not_found", ["product" => $product->title]));
            }

            if (!in_array($item['selected_size'], $product->sizes)) {
                throw new Exception(__("custom.shop.not_access_register_order_product_size_not_found", ["product" => $product->title]));
            }

            $allItems[] = new OrderItem([
                'product_id' => $item['product_id'],
                'selected_size' => $item['selected_size'],
                'count' => $item['count'],
                'unit_price' => $product->price,
                'total_price' => $product->price * $item['count'],
            ]);
        }

        return $allItems;
    }

    /**
     * @return string
     */
    public function generateTrackingCode(): string
    {
        $min = 1000;
        $max = 9999;
        $code = substr((string)time(), 6, 10) . rand($min, $max);


        while (true) {
            $existInvoice = DB::table("orders")->where("tracking_number", $code)->exists();
            if ($existInvoice) {
                $code = substr((string)time(), 6, 10) . rand($min, $max);
            } else {
                break;
            }
        }

        return $code;
    }
}
