<?php

namespace App\Services;

use App\Http\Repositories\UserClothingRepository;
use App\Http\Repositories\UserRepository;
use App\Jobs\SendRedisMessage;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UserClothesService
{
    public $user;

    /**
     * @throws Exception
     */
    public function __construct(public UserClothingRepository $repository)
    {
        $userItem = Auth::user();
        if (!$userItem) {
            throw new Exception(__("custom.user.not_exist"));
        }
        $this->user = $userItem;
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
     * @param $inputs
     * @return bool
     * @throws Exception
     */
    public function uploadClothingImage($inputs): mixed
    {
        $inputs["image"] = $this->saveImage($inputs["image"], 'clothes_images');
        $inputs["process_status"] = 1;
        $inputs["user_id"] = $this->user->id;

        DB::beginTransaction();
        try {
            $createdItem = $this->repository->create($inputs);

            $data = [
                "action" => "process",
                "user_id" => $this->user->id,
                "image_link" => asset($inputs["image"]),
                "gender" => $this->user->gender,
                "clothes_id" => $createdItem->id,
                "time" => Carbon::now()->format("H:i:s"),
            ];
            SendRedisMessage::dispatch($data);

            DB::commit();
            return $createdItem;
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception(__("custom.user.register_exception"));
        }
    }


    /**
     * @param $clothesId
     * @return bool
     * @throws Exception
     */
    public function delete($clothesId): bool
    {
        $item = $this->repository->findWithInputs(["id" => $clothesId, "user_id" => Auth::id()]);
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
                $path = 'user/' . $folder;
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
