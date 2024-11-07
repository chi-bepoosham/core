<?php

namespace App\Services;


use Ipe\Sdk\Facades\SmsIr;
use Illuminate\Support\Facades\Log;

class SmsService
{

    public static function sendSms($mobile)
    {
        $templateId = 621415;
        $parameters = [
            [
                "name" => "TOKEN",
                "value" => "1234"
            ]
        ];

        try {
            $send =  SmsIr::verifySend($mobile, $templateId, $parameters);
            if ($send->status == 1){
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
