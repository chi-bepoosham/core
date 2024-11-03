<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\Response;

class UsersService
{
    public function __construct(public UserRepository $repository)
    {
    }


    /**
     * @param $inputs
     * @return bool
     * @throws Exception
     */
    public function updateUser($inputs): bool
    {
        $userItem = $this->repository->find(Auth::id());
        if (!$userItem) {
            throw new Exception(__("custom.user.not_exist"));
        }

        $deleteAvatar = $inputs["delete_avatar"] ?? null;
        if ((bool)$deleteAvatar === true) {
            $inputs["avatar"] = null;
        }
        $inputs["birthday"] = $inputs["birthday"] ?? null;
        $inputs["email"] = $inputs["email"] ?? null;

        if (isset($inputs["avatar"])) {
            $inputs["avatar"] = $this->saveImage($inputs["avatar"]);
        }

        DB::beginTransaction();
        try {
            $createdItem = $this->repository->update($userItem, $inputs);
            DB::commit();
            return $createdItem;
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception(__("custom.user.register_exception"));
        }
    }


    public function saveImage($imageFile): ?string
    {
        try {
            $extension = $imageFile->getClientOriginalExtension();
            if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'wep' || $extension == 'webp' || $extension == 'svg') {

                $newFilePath = $imageFile->getPath() . '/' . time() . rand(1, 99) . '.' . $extension;
                $imageEncoded = Image::make($imageFile->getRealPath())->save($newFilePath, 50);

                $image = new UploadedFile($imageEncoded->basePath(), $imageFile->getFilename());


                $imageName = uniqid() . time() . random_string() . '.' . $extension;
                $path = 'user/avatar';
                $fullPath = Storage::disk('public')->putFileAs(path: $path, file: $image, name: $imageName, options: ['visibility' => 'public', 'directory_visibility' => 'public']);

                try {
                    unlink($newFilePath);
                } catch (Exception $exception) {
                }

                return asset(Storage::url($fullPath));
            }

            return null;

        } catch (Exception $exception) {
            return null;
        }
    }

}
