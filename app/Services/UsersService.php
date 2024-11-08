<?php

namespace App\Services;

use App\Jobs\SendRabbitMQMessage;
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
    public $user;

    /**
     * @throws Exception
     */
    public function __construct(public UserRepository $repository)
    {
        $userItem = Auth::user();
        if (!$userItem) {
            throw new Exception(__("custom.user.not_exist"));
        }
        $this->user = $userItem;
    }


    /**
     * @param $inputs
     * @return bool
     * @throws Exception
     */
    public function updateUser($inputs): bool
    {
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

        if (isset($inputs["avatar"])) {
            $inputs["avatar"] = $this->saveImage($inputs["avatar"], 'avatar');
        }

        DB::beginTransaction();
        try {
            $createdItem = $this->repository->update($this->user, $inputs);
            DB::commit();
            return $createdItem;
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
        if ($this->user->process_body_image_status == 1){
            throw new Exception(__("custom.user.body_type_not_detected"));
        }

        if ($this->user->process_body_image_status == 2){
            throw new Exception(__("custom.user.body_type_not_detected"));
        }

        $inputs["body_image"] = $this->saveImage($inputs["image"], 'body_images');
        $inputs["process_body_image_status"] = 1;

        DB::beginTransaction();
        try {
            $createdItem = $this->repository->update($this->user, $inputs);

            $data = [
                "action" => "body_type",
                "user_id" => $this->user->id,
                "image_link" => asset($inputs["body_image"]),
                "gender" => $this->user->gender,
                "clothes_id" => null,
                "time" => Carbon::now()->format("H:i:s"),
            ];
            SendRabbitMQMessage::dispatch($data);

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
        $userBodyType = $this->user->bodyType()->with(["celebrities", "clothes"])->first();
        if ($userBodyType != null) {
            $result = new \stdClass();
            $result->body_type = $userBodyType;
            return $result;
        }

        throw new Exception(__("custom.user.body_type_not_detected"),409);
    }


    public function saveImage($imageFile, $folder): ?string
    {
        try {
            $extension = $imageFile->getClientOriginalExtension();
            if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'wep' || $extension == 'webp' || $extension == 'svg') {

                $newFilePath = $imageFile->getPath() . '/' . time() . rand(1, 99) . '.' . $extension;
                $imageEncoded = Image::make($imageFile->getRealPath())->save($newFilePath, 50);

                $image = new UploadedFile($imageEncoded->basePath(), $imageFile->getFilename());


                $imageName = sha1(md5(Auth::id())) . '.' . $extension;
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
