<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
use App\Http\Repositories\UserRepository;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

class AuthenticationsService
{
    public function __construct(public UserRepository $repository)
    {
    }

    /**
     * @param $inputs
     * @return bool
     * @throws Exception|InvalidArgumentException
     */
    public function sendOtp($inputs): bool
    {
        $mobile = $inputs["mobile"];
        $code = $this->generateOtpRandom();
        $hash = $fields["hash"] ?? '-';
        $ttlCache = intval(1 * 60);
        $expiredTime = now()->addMinutes($ttlCache);

        try {
            sendSms($mobile, $code, $hash);
            cache()->set($mobile, ['code' => $code, 'expired_time' => $expiredTime], $ttlCache);
            return true;
        } catch (Exception) {
            throw new Exception(__("custom.user.send_otp_failed"));
        }

    }


    /**
     * @param $inputs
     * @return array
     * @throws Exception|InvalidArgumentException
     */
    #[ArrayShape(['token' => "mixed", 'user' => "mixed", 'api_key' => "mixed"])]
    public function otpConfirm($inputs): array
    {
        $mobile = $inputs["mobile"];
        $user = User::query()->where("mobile", $mobile)->first();
        if ($user) {
            cache()->forget($mobile);
            $userId = $user->id;
            $token = $user->createToken("ChiBepoosham-usr-$userId")->plainTextToken;
            return ['token' => $token, 'user' => $user];
        }

        $apiKeyCache = $mobile . "-api-key";
        $ttlCache = intval(30 * 60);
        $expiredTime = now()->addMinutes($ttlCache);
        $apiKey = Str::uuid();
        cache()->set($apiKeyCache, ['api_key' => $apiKey, 'expired_time' => $expiredTime], $ttlCache);

        return ['token' => null, 'user' => null, 'api_key' => $apiKey];
    }


    /**
     * @param $inputs
     * @return array
     * @throws Exception
     */
    #[ArrayShape(['token' => "mixed", 'user' => "mixed", 'has_list' => "false", 'has_group' => "false"])]
    public function register($inputs): array
    {
        DB::beginTransaction();
        try {

            if (isset($inputs["avatar"])) {
                $userService = new UsersService(new UserRepository());
                $inputs["avatar"] = $userService->saveImage($inputs["avatar"], 'avatar');
            }

            $createdItem = $this->create($inputs);
            $user = User::query()->find($createdItem->id);
            $userId = $user->id;
            $token = $user->createToken("ChiBepoosham-usr-$userId")->plainTextToken;
            DB::commit();

            return ['token' => $token, 'user' => $user];

        } catch (Exception) {
            DB::rollBack();
            throw new Exception(__("custom.user.register_exception"));
        }
    }

    /**
     * @param $inputs
     * @return array
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
            throw new Exception(__("custom.user.create_user_exception"));
        }
    }


    /**
     * @return string
     * @throws Exception
     */
    public function callbackGoogleOAuth(): string
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            throw new Exception(__("custom.user.oauth_google_exception"));
        }

        $user = User::query()->where("email", $googleUser->getEmail())->first();

        if ($user) {
            $userId = $user->id;
            return $user->createToken("ChiBepoosham-usr-$userId")->plainTextToken;
        } else {

            $inputs = [
                'first_name' => $googleUser->user['given_name'],
                'last_name' => $googleUser->user['family_name'],
                'email' => $googleUser->getEmail(),
                'email_verified_at' => now()
            ];


            DB::beginTransaction();
            try {

                $createdItem = $this->create($inputs);
                $user = User::query()->find($createdItem->id);
                $userId = $user->id;
                $token = $user->createToken("ChiBepoosham-usr-$userId")->plainTextToken;
                DB::commit();

                return $token;

            } catch (Exception) {
                DB::rollBack();
                throw new Exception(__("custom.user.register_exception"));
            }
        }
    }


    public function generateOtpRandom(): int
    {
        if (env('APP_ENV') == 'production') {
            $minNumberRandom = 11000;
            $maxNumberRandom = 99999;
            return rand($minNumberRandom, $maxNumberRandom);
        } else {
            return 11111;
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function checkApiKey($apiKey, $mobile): bool
    {
        $apiKeyCache = $mobile . "-api-key";
        if (cache()->has($apiKeyCache)) {
            $cacheData = cache()->get($apiKeyCache);
            $apiKeyValue = $cacheData['api_key'] ?? null;
            $expiredTime = $cacheData['expired_time'] ?? null;
            return $apiKey == $apiKeyValue && (bool)Carbon::make($expiredTime) >= now();
        }
        return false;
    }

}
