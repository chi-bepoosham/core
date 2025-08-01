<?php

namespace App\Services;

use App\Http\Repositories\OrderPaymentRepository;
use App\Http\Repositories\OrderRepository;
use App\Http\Repositories\ProductRepository;
use App\Http\Repositories\ShopRepository;
use App\Http\Repositories\UserAddressRepository;
use App\Models\OrderItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
use stdClass;

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
        if (isset(request()->userShop)) {
            $inputs["shop_id"] = Auth::id();
        } elseif (!isset(request()->userAdmin) && !isset(request()->userShop)) {
            $inputs["user_id"] = Auth::id();
        }

        $filterDates = [];

        if (empty($relations)) {
            $relations = ['shop', 'items'];
        }

        if (isset($inputs['from_date']) && isset($inputs['to_date'])) {
            $filterDates = [Carbon::parse($inputs['from_date'])->startOfDay(), Carbon::parse($inputs['to_date'])->endOfDay()];
        }

        $query = $this->repository->queryFull(inputs: $inputs, relations: $relations);

        if (!empty($filterDates)) {
            $query->whereBetween('created_at', $filterDates);
        }

        return $this->repository->resolve_paginate(query: $query);
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

        if (isset(request()->userShop) && $item->shop_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        } elseif (!isset(request()->userAdmin) && !isset(request()->userShop) && $item->user_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }


        return $item;
    }

    /**
     * @param $inputs
     * @return stdClass
     * @throws Exception
     */
    public function register($inputs): stdClass
    {
        if (isset(request()->userAdmin)) {
            $userId = $inputs["user_id"];
        } else {
            $userId = Auth::id();
        }

        $inputs["user_id"] = $userId;

        $shop = (new ShopRepository())->find($inputs["shop_id"]);

        $userAddress = (new UserAddressRepository())->find($inputs["user_address_id"]);
        if ($userAddress == null || $userAddress->user_id != $userId) {
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

            $payment = $this->createOrderPayment($createdItem);

            DB::commit();

            $order = $this->show($createdItem->id);

            $result = new stdClass();
            $result->payment_gateway = json_decode($payment);
            $result->order = $order;

            return $result;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.store_failed"));
        }
    }


    /**
     * @param $orderId
     * @return stdClass
     * @throws Exception
     */
    public function payOrder($orderId): stdClass
    {
        $item = $this->repository->findWithRelations($orderId);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        if ($item->progress_status != 'pendingForPayment') {
            throw new Exception(__("custom.shop.not_access_pay_order_progress_status"));
        }

        if (!isset(request()->userAdmin) && $item->user_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }

        DB::beginTransaction();
        try {

            $payment = $this->createOrderPayment($item);

            DB::commit();

            $result = new stdClass();
            $result->payment_gateway = json_decode($payment);
            $result->order = $item;

            return $result;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.index_failed"));
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

        if (isset(request()->userShop) && $item->shop_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        } elseif (!isset(request()->userAdmin) && !isset(request()->userShop) && $item->user_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }


        if (isset($inputs["user_address_id"])) {
            if (!isset(request()->userShop) && !isset(request()->userAdmin)) {
                $userAddress = (new UserAddressRepository())->find($inputs["user_address_id"]);
                if ($userAddress == null || $userAddress->user_id != Auth::id()) {
                    throw new Exception(__("custom.shop.not_access_register_order_user_address"));
                }
            }
        }

        if (isset($inputs["status"])) {

            if ($item->status != $inputs["status"] && $item->status != "inProgress") {

                if ($item->status == config('order.Status.Delivered')) {
                    throw new Exception(__("custom.shop.not_access_update_order_delivered"));
                }
                if ($item->status == config('order.Status.Returned')) {
                    throw new Exception(__("custom.shop.not_access_update_order_returned"));
                }
                if ($item->status == config('order.Status.Canceled')) {
                    throw new Exception(__("custom.shop.not_access_update_order_canceled"));
                }

            }

            if ($inputs["status"] == config('order.Status.Delivered')) {
                $inputs["progress_status"] = config('order.ProgressStatus.Delivered');
            }
            if ($inputs["status"] == config('order.Status.Returned')) {
                $inputs["progress_status"] = config('order.ProgressStatus.Returned');
            }
            if ($inputs["status"] == config('order.Status.Canceled')) {
                $inputs["progress_status"] = config('order.ProgressStatus.Canceled');
            }

        }

        DB::beginTransaction();
        try {
            $this->repository->update($item, $inputs);
            $order = $this->repository->findWithRelations($orderId);

            if (isset($inputs["progress_status"]) && $inputs["progress_status"] == config('order.ProgressStatus.Canceled')) {
                WalletsService::withdrawalFromWallet($orderId, config('wallet.TransactionTypes.CancelOrder'));
            }

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

        if (!isset(request()->userAdmin)) {
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
     * @throws Exception
     */
    public function createOrderPayment($order): string
    {
        $orderInvoice = new Invoice();
        $orderInvoice->uuid($order->id);
        $orderInvoice->amount($order->final_price);
        return Payment::purchase($orderInvoice, function ($driver, $transactionId) use ($order) {
            $orderPaymentRepository = new OrderPaymentRepository();
            $orderPaymentRepository->create([
                "order_id" => $order->id,
                "transaction_id" => $transactionId,
                "amount" => $order->final_price,
            ]);
        })->pay()->toJson();
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
