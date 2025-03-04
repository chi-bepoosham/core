<?php

namespace App\Services;

use App\Http\Repositories\UserAddressRepository;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class UserAddressesService
{

    public function __construct(public UserAddressRepository $repository)
    {
    }

    /**
     * @return Collection|LengthAwarePaginator
     */
    public function index(): Collection|LengthAwarePaginator
    {
        $inputs["user_id"] = Auth::id();
        return $this->repository->resolve_paginate(inputs: $inputs, relations: ['city', 'province']);
    }

    /**
     * @param $addressId
     * @return object|null
     * @throws Exception
     */
    public function show($addressId): ?object
    {
        $item = $this->repository->findWithRelations($addressId);
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
    public function create($inputs): mixed
    {
        $inputs["user_id"] = Auth::id();

        DB::beginTransaction();
        try {
            $createdItem = $this->repository->create($inputs);
            $address = $this->repository->findWithRelations($createdItem->id);
            DB::commit();
            return $address;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.store_failed"));
        }
    }

    /**
     * @param $inputs
     * @param $addressId
     * @return mixed
     * @throws Exception
     */
    public function update($inputs, $addressId): mixed
    {
        $item = $this->repository->find($addressId);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        if ($item->user_id != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }


        DB::beginTransaction();
        try {
            $this->repository->update($item, $inputs);
            $address = $this->repository->findWithRelations($addressId);
            DB::commit();
            return $address;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.update_failed"));
        }
    }

    /**
     * @param $addressId
     * @return bool
     * @throws Exception
     */
    public function delete($addressId): bool
    {
        $item = $this->repository->find($addressId);
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



}
