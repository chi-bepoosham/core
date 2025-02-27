<?php

namespace App\Services;

use App\Http\Repositories\ShopRepository;
use App\Jobs\SendRedisMessage;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ShopsService
{

    public function __construct(public ShopRepository $repository)
    {
    }

    /**
     * @param $inputs
     * @return Collection|LengthAwarePaginator
     */
    public function index($inputs): Collection|LengthAwarePaginator
    {
        $inputs["user_id"] = Auth::id();
        return $this->repository->resolve_paginate(inputs: $inputs, relations: $this->repository->relations());
    }



    /**
     * @param $shopId
     * @return bool
     * @throws Exception
     */
    public function delete($shopId): bool
    {
        $item = $this->repository->findWithInputs(["id" => $shopId, "user_id" => Auth::id()]);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        DB::beginTransaction();
        try {
            $this->repository->delete($item);
            DB::commit();
            return true;
        } catch (Exception $exception) {
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
                } catch (Exception $exception) {
                }

                return "/storage/" . $fullPath;
            }

            return null;

        } catch (Exception $exception) {
            return null;
        }
    }


}
