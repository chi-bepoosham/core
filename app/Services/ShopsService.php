<?php

namespace App\Services;

use App\Http\Repositories\ShopRepository;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ShopsService
{

    public function __construct(public ShopRepository $repository)
    {
    }


    /**
     * @return \stdClass
     */
    public function splash(): \stdClass
    {
        $shop = $this->repository->find(Auth::id());

        $data = new \stdClass();
        $data->shop = $shop;
        $data->shop->province = $shop->province;
        $data->shop->city = $shop->city;
        return $data;
    }


    /**
     * @param $inputs
     * @return Collection|LengthAwarePaginator
     */
    public function index($inputs): Collection|LengthAwarePaginator
    {
        return $this->repository->resolve_paginate(inputs: $inputs, relations: ['province', 'city']);
    }

    /**
     * @param $shopId
     * @return object|null
     * @throws Exception
     */
    public function show($shopId): ?object
    {
        if (!isset(request()->userAdmin) && $shopId != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }

        $item = $this->repository->findWithRelations($shopId);
        if ($item == null) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        return $item;
    }


    /**
     * @param $inputs
     * @param $shopId
     * @return mixed
     * @throws Exception
     */
    public function update($inputs, $shopId): mixed
    {
        if (!isset(request()->userAdmin) && $shopId != Auth::id()) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }

        $item = $this->repository->find($shopId);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }


        if (isset($inputs["logo"])) {
            $inputs["logo"] = $this->saveImage($inputs["logo"], 'logo');
        }

        if (isset($inputs["location_lat"]) && isset($inputs["location_lng"])) {
            $inputs["location_point"] = $inputs["location_lat"] . ',' . $inputs["location_lng"];
        }

        if (isset($inputs["password"])) {
            $inputs["password"] = Hash::make($inputs["mobile"]);
        }

        DB::beginTransaction();
        try {
            $this->repository->update($item, $inputs);
            DB::commit();
            return $item;
        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.defaults.update_failed"));
        }
    }

    /**
     * @param $shopId
     * @return bool
     * @throws Exception
     */
    public function delete($shopId): bool
    {
        if (!isset(request()->userAdmin)) {
            throw new Exception(__("exceptions.exceptionErrors.accessDenied"));
        }

        $item = $this->repository->find($shopId);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
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


    public function saveImage($imageFile, $folder): ?string
    {
        try {
            $extension = $imageFile->getClientOriginalExtension();
            if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'wep' || $extension == 'webp' || $extension == 'svg') {

                $newFilePath = $imageFile->getPath() . '/' . time() . rand(1, 99) . '.' . $extension;
                $imageEncoded = Image::make($imageFile->getRealPath())->save($newFilePath, 50);

                $image = new UploadedFile($imageEncoded->basePath(), $imageFile->getFilename());


                $imageName = sha1(md5(Auth::id())) . time() . rand(100, 999) . '.' . $extension;
                $path = 'shop/' . $folder;
                $fullPath = Storage::putFileAs(path: $path, file: $image, name: $imageName, options: ['visibility' => 'public', 'directory_visibility' => 'public']);

                try {
                    unlink($newFilePath);
                } catch (Exception) {
                }

                return "/storage/" . $fullPath;
            }

            return null;

        } catch (Exception) {
            return null;
        }
    }


}
