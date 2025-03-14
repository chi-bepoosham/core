<?php

namespace App\Services;

use App\Http\Repositories\OrderRepository;
use App\Http\Repositories\WalletRepository;
use App\Http\Repositories\WalletTransactionRepository;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletsService
{

    public function __construct(public WalletRepository $repository)
    {
    }

    /**
     * @param $inputs
     * @return Collection|LengthAwarePaginator
     */
    public function index($inputs): Collection|LengthAwarePaginator
    {
        return $this->repository->resolve_paginate(inputs: $inputs);
    }


    /**
     * @param $shopId
     * @return object|null
     * @throws Exception
     */
    public function show($shopId): ?object
    {
        $item = $this->repository->findWithInputs(['shop_id' => $shopId], ['transactions']);
        if ($item == null) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        if (isset(request()->userShop) && $item->shop_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }

        return $item;
    }

    /**
     * @param $inputs
     * @return mixed
     * @throws Exception
     */
    public function create($inputs): mixed
    {
        DB::beginTransaction();
        try {
            $createdItem = $this->repository->create($inputs);
            DB::commit();
            return $createdItem;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.store_failed"));
        }
    }


    /**
     * @param $shopId
     * @return bool
     * @throws Exception
     */
    public function delete($shopId): bool
    {
        $item = $this->repository->findWithInputs(['shop_id' => $shopId]);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        if (isset(request()->userShop) && $item->shop_id != Auth::id()) {
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
     * @throws Exception
     */
    public static function withdrawalFromWallet($orderId, $type): mixed
    {
        $order = (new OrderRepository())->find($orderId);
        $walletTransaction = (new WalletTransactionRepository())->findWithInputs(['order_id' => $orderId]);

        if ($type == config('wallet.TransactionTypes.CancelOrder')) {
            $descriptionTitle = config('wallet.Descriptions.CancelOrder');
        } else {
            $descriptionTitle = config('wallet.Descriptions.ReturnOrder');
        }

        $walletId = $walletTransaction->wallet_id;
        $finalAmount = $walletTransaction->amount;

        $description = __($descriptionTitle,
            [
                'Amount' => $finalAmount,
                'OrderId' => $order->tracking_number,
            ]);


        $inputs = [
            'wallet_id' => $walletId,
            'type' => $type,
            'order_id' => $orderId,
            'amount' => $finalAmount,
            'date_time' => now(),
            'description' => $description,
        ];


        $newWalletTransaction = (new WalletTransactionRepository())->create($inputs);

        # remove revenues
        RevenuesService::RemoveRevenues($walletTransaction->id);

        return $newWalletTransaction;
    }

    /**
     * @throws Exception
     */
    public static function depositToWallet($orderId): mixed
    {
        $order = (new OrderRepository())->find($orderId);
        $shop = $order->shop;
        $wallet = (new WalletRepository())->findWithInputs(['shop_id' => $shop->id]);
        $commissionPercent = $shop->commission_percent;
        $amount = $order->final_price;
        $type = config('wallet.TransactionTypes.Order');

        $commissionAmount = ceil($amount * $commissionPercent / 100);
        $finalAmount = $amount - $commissionAmount;

        $description = __(config('wallet.Descriptions.DepositOrder'),
            [
                'Amount' => $finalAmount,
                'OrderId' => $order->tracking_number,
                'CommissionAmount' => $commissionAmount,
            ]);

        $inputs = [
            'wallet_id' => $wallet->id,
            'type' => $type,
            'order_id' => $orderId,
            'amount' => $finalAmount,
            'date_time' => now(),
            'description' => $description,
        ];


        $walletTransaction = (new WalletTransactionRepository())->create($inputs);

        # register revenues
        RevenuesService::RegisterRevenues($walletTransaction->id, config('revenues.RevenuesTypes.Order'), $commissionAmount);

        return $walletTransaction;
    }

}
