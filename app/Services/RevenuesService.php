<?php

namespace App\Services;

use App\Http\Repositories\RevenuesRepository;
use App\Http\Repositories\WalletTransactionRepository;
use App\Models\Revenues;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RevenuesService
{

    public function __construct(public RevenuesRepository $repository)
    {
    }

    /**
     * @param $inputs
     * @return Collection|LengthAwarePaginator
     */
    public function index($inputs): Collection|LengthAwarePaginator
    {

        if (isset($inputs['from_date']) && isset($inputs['to_date'])) {
            $filterDates = [Carbon::parse($inputs['from_date'])->startOfDay(), Carbon::parse($inputs['to_date'])->endOfDay()];
        }

        $query = $this->repository->queryFull(inputs: $inputs);

        if (!empty($filterDates)) {
            $query->whereBetween('date_time', $filterDates);
        }

        return $this->repository->resolve_paginate(query: $query);
    }


    /**
     * @param $revenuesId
     * @return object|null
     * @throws Exception
     */
    public function show($revenuesId): ?object
    {
        $item = $this->repository->findWithRelations($revenuesId);
        if ($item == null) {
            throw new Exception(__("custom.defaults.not_found"));
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
     * @param $revenuesId
     * @return bool
     * @throws Exception
     */
    public function delete($revenuesId): bool
    {
        $item = $this->repository->find($revenuesId);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        return self::RemoveRevenues($item->wallet_transaction_id);
    }


    /**
     * @throws Exception
     */
    public static function RegisterRevenues($walletTransactionId, $type, $amount)
    {
        $walletTransaction = (new WalletTransactionRepository())->findWithRelations($walletTransactionId);
        $order = $walletTransaction->order;

        if ($type == config('revenues.RevenuesTypes.Order')) {
            $description = __(config('revenues.Descriptions.Order'),
                [
                    'Amount' => $amount,
                    'OrderId' => $order->tracking_number,
                ]);
        } else {
            $shop = $order->shop;
            $description = __(config('revenues.Descriptions.Ads'),
                [
                    'Amount' => $amount,
                    'Shop' => $shop->name,
                ]);
        }


        $inputs = [
            'wallet_transaction_id' => $walletTransactionId,
            'type' => $type,
            'amount' => $amount,
            'date_time' => now(),
            'description' => $description,
        ];


        return (new RevenuesRepository())->create($inputs);
    }


    /**
     * @throws Exception
     */
    public static function RemoveRevenues($walletTransactionId): true
    {
        $walletTransaction = Revenues::query()->where('wallet_transaction_id', $walletTransactionId)->withTrashed()->first();

        DB::beginTransaction();
        try {
            (new RevenuesRepository())->delete($walletTransaction);
            DB::commit();
            return true;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.delete_failed"));
        }
    }

}
