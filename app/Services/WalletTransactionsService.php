<?php

namespace App\Services;

use App\Http\Repositories\WalletRepository;
use App\Http\Repositories\WalletTransactionRepository;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletTransactionsService
{

    public function __construct(public WalletTransactionRepository $repository)
    {
    }

    /**
     * @param $inputs
     * @return Collection|LengthAwarePaginator
     */
    public function index($inputs): Collection|LengthAwarePaginator
    {
        $filterDates = [];
        if (isset($inputs['from_date']) && isset($inputs['to_date'])) {
            $filterDates = [Carbon::parse($inputs['from_date'])->startOfDay(), Carbon::parse($inputs['to_date'])->endOfDay()];
        }

        $query = $this->repository->queryFull(inputs: $inputs);

        if (isset($inputs['shop_id'])) {
            $query
                ->join('wallets', 'wallet_transactions.wallet_id', '=', 'wallets.id')
                ->where('wallets.shop_id', $inputs['shop_id']);
        }

        if (!empty($filterDates)) {
            $query->whereBetween('date_time', $filterDates);
        }

        $query->select('wallet_transactions.*')->distinct();

        return $this->repository->resolve_paginate(query: $query);
    }


    /**
     * @param $walletTransactionId
     * @return object|null
     * @throws Exception
     */
    public function show($walletTransactionId): ?object
    {
        $item = $this->repository->findWithRelations($walletTransactionId, ['wallet']);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        if (isset(request()->userShop) && $item->wallet->shop_id != Auth::id()) {
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
        if (!isset($inputs['wallet_id'])) {
            $wallet = (new WalletRepository())->findWithInputs(['shop_id' => $inputs['shop_id']]);
            if (!$wallet) {
                throw new Exception(__("custom.wallet.not_exist"));
            }
            $inputs['wallet_id'] = $wallet->id;
        }

        if (isset($inputs['date_time'])) {
            $inputs['date_time'] = Carbon::parse($inputs['date_time']);
        }


        DB::beginTransaction();
        try {
            $createdItem = $this->repository->create($inputs);
            $this->calculateAndUpdateWalletBalance($createdItem->wallet_id);
            DB::commit();
            return $createdItem;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.store_failed"));
        }
    }


    /**
     * @param $inputs
     * @param $walletTransactionId
     * @return mixed
     * @throws Exception
     */
    public function update($inputs, $walletTransactionId): mixed
    {
        $item = $this->repository->findWithRelations($walletTransactionId, ['wallet']);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }


        if (isset($inputs['date_time'])) {
            $inputs['date_time'] = Carbon::parse($inputs['date_time']);
        }


        DB::beginTransaction();
        try {
            $this->repository->update($item, $inputs);
            $this->calculateAndUpdateWalletBalance($item->wallet_id);

            $walletTransaction = $this->repository->findWithRelations($walletTransactionId);

            DB::commit();
            return $walletTransaction;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.store_failed"));
        }
    }


    /**
     * @param $walletTransactionId
     * @return bool
     * @throws Exception
     */
    public function delete($walletTransactionId): bool
    {
        $item = $this->repository->findWithRelations($walletTransactionId, ['wallet']);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        DB::beginTransaction();
        try {
            $this->repository->delete($item);
            $this->calculateAndUpdateWalletBalance($item->wallet_id);
            DB::commit();
            return true;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.delete_failed"));
        }
    }

    public function calculateAndUpdateWalletBalance($walletId): void
    {

        $allTransactions = WalletTransaction::query()->where('wallet_id', $walletId)->get();
        $sumDeposit = $allTransactions->whereIn('type', ['order'])->sum('amount');
        $sumWithdrawal = $allTransactions->whereIn('type', ['cancel_order', 'return_order', 'ads', 'withdraw'])->sum('amount');

        $finalWalletBalance = $sumDeposit - $sumWithdrawal;

        Wallet::query()->where('id', $walletId)->update(['balance' => $finalWalletBalance]);
    }

}
