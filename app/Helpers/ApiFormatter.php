<?php

namespace App\Helpers;

class ApiFormatter
{
    protected static $simpleFormat = [
        "success" => null,
        "message" => null,
    ];

    protected static $dataFormat = [
        "success" => null,
        "data" => null,
    ];

    protected static $accessTokenFormat = [
        "success" => null,
        "access_token" => null,
    ];

    public static function response($success = null, $message = null, $code = 200)
    {
        self::$simpleFormat["success"] = $success;
        self::$simpleFormat["message"] = $message;
        return response()->json(self::$simpleFormat, $code);
    }

    public static function responseWithData($success = null, $data = null, $code = 200)
    {
        self::$dataFormat["success"] = $success;
        self::$dataFormat["data"] = $data;
        return response()->json(self::$dataFormat, $code);
    }

    public static function accessTokenResponse($success = null, $accessToken = null, $code = 200)
    {
        self::$accessTokenFormat["success"] = $success;
        self::$accessTokenFormat["access_token"] = $accessToken;
        return response()->json(self::$accessTokenFormat, $code);
    }
}

?>