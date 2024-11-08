<?php

namespace App\Services;


use Ipe\Sdk\Facades\SmsIr;
use Illuminate\Support\Facades\Log;

class SmsService
{

    public static function sendSms($mobile, $code)
    {
        $templateId = env("SMSIR_LINE_NUMBER", 621415);
        $parameters = [
            [
                "name" => "TOKEN",
                "value" => $code,
            ]
        ];

        try {
            $send = SmsIr::verifySend($mobile, $templateId, $parameters);
            Log::debug(json_encode($send));
            if ($send->status == 1) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
