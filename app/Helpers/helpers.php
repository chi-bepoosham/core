<?php

use Illuminate\Support\Facades\Log;

if (!function_exists('random_string')) {
    function random_string($length = 10, $start_with = '', $end_with = ''): string
    {
        $start_with = filled($start_with) ? $start_with . "_" : $start_with;
        $end_with = filled($end_with) ? "_" . $end_with : $end_with;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $start_with . $randomString . $end_with;
    }
}

if (!function_exists('sendSms')) {
    function sendSms(string|int $mobile, string $code = null, string $hash = '-'): bool
    {
        try {
            $app_env = env('APP_ENV');
            Log::info($mobile." : ".$code);
            if ($app_env == 'production') {
                return App\Services\SmsService::sendSms($mobile,$code);
            } else {
                return true;
            }
        } catch (\Exception $exception) {
            return false;
        }
    }
}

if (!function_exists('is_string_persian')) {
    function is_string_persian(string $string): bool|int
    {
        $pattern = '/^[\x{0600}-\x{06FF}\x{FB50}-\x{FDFF}\x{06F0}-\x{06F9}0-9\s\.,،]+$/u';
        return preg_match($pattern, $string) === 1;
    }
}
