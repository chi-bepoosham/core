<?php

namespace App\Services;

use App\Http\Repositories\WalletRepository;
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

}
