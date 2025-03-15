<?php

namespace App\Services;

use App\Jobs\SendRedisMessage;
use App\Jobs\SendRequestProcessImage;
use App\Models\UserBodyTypeHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\Response;

class UsersService
{

    /**
     * @throws Exception
     */
    public function __construct(public UserRepository $repository)
    {
    }

    /**
     * @return \stdClass
     */
    public function splash(): \stdClass
    {
        $user = Auth::user();

        $data = new \stdClass();
        $data->user = $user;
        $data->user->body_type = $user->bodyType;
        return $data;
    }


    /**
     * @param $inputs
     * @return mixed
     * @throws Exception
     */
    public function updateUser($inputs): mixed
    {
        $userItem = Auth::user();
        if (!$userItem) {
            throw new Exception(__("custom.user.not_exist"));
        }

        $mobile = $inputs["mobile"];
        $existUserItem = User::query()->whereNot("id", Auth::id())->where("mobile", $mobile)->first();
        if ($existUserItem != null) {
            throw new Exception(__("custom.user.mobile_exist"));
        }

        $deleteAvatar = $inputs["delete_avatar"] ?? null;
        if ((bool)$deleteAvatar === true) {
            $inputs["avatar"] = null;
        }
        $inputs["birthday"] = $inputs["birthday"] ?? null;
        $inputs["email"] = $inputs["email"] ?? null;


        if ($inputs["email"] != null) {
            $existUserItem = User::query()->whereNot("id", Auth::id())->where("email", $inputs["email"])->first();
            if ($existUserItem != null) {
                throw new Exception(__("custom.user.email_exist"));
            }
        }


        if (isset($inputs["avatar"])) {
            $inputs["avatar"] = $this->saveImage($inputs["avatar"], 'avatar');
        }

        DB::beginTransaction();
        try {
            $createdItem = $this->repository->update($userItem, $inputs);
            DB::commit();
            return $this->repository->findWithRelations(Auth::id());
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception(__("custom.user.register_exception"));
        }
    }


    /**
     * @param $inputs
     * @return bool
     * @throws Exception
     */
    public function updateBodyImage($inputs): bool
    {
        $userItem = Auth::user();
        if (!$userItem) {
            throw new Exception(__("custom.user.not_exist"));
        }

        if ($userItem->process_body_image_status == 1) {
            throw new Exception(__("custom.user.body_type_not_detected"));
        }

        $inputs["body_image"] = $this->saveImage($inputs["image"], 'body_images');
        $inputs["process_body_image_status"] = 1;

        DB::beginTransaction();
        try {
            $createdItem = $this->repository->update($userItem, $inputs);

            UserBodyTypeHistory::query()->create([
                "user_id" => $userItem->id,
                "body_image" => $inputs["body_image"],
                "user_data" => json_encode($userItem),
            ]);

            $data = [
                "image_url" => asset($inputs["body_image"]),
                "gender" => $userItem->gender,
            ];
            SendRequestProcessImage::dispatch(data: $data, type: 'bodyType', userId: $userItem->id);

//            $data = [
//                "action" => "body_type",
//                "user_id" => $userItem->id,
//                "image_link" => asset($inputs["body_image"]),
//                "gender" => $userItem->gender,
//                "clothes_id" => null,
//                "time" => Carbon::now()->format("H:i:s"),
//            ];
//            SendRedisMessage::dispatch($data);


            DB::commit();
            return $createdItem;
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception(__("custom.user.register_exception"));
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function getBodyTypeDetail(): mixed
    {
        $userItem = Auth::user();
        if (!$userItem) {
            throw new Exception(__("custom.user.not_exist"));
        }

        $userBodyType = $userItem->bodyType()->with(["celebrities", "clothes"])->first();
        if ($userBodyType != null) {
            $result = new \stdClass();
            $result->body_type = $userBodyType;
            return $result;
        }

        throw new Exception(__("custom.user.body_type_not_detected"), 409);
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
