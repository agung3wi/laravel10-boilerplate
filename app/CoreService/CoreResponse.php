<?php

namespace App\CoreService;

class CoreResponse
{

    public static function ok($output, $message = "")
    {
        return response()->json($output, 200);
    }

    public static function fail($ex)
    {
        $result["success"] = false;
        if (!empty($ex->getErrorMessage()) && !is_null($ex->getErrorMessage())) {
            $result["message"] = $ex->getErrorMessage();
        }

        return response()->json($result, $ex->getErrorCode());
    }

    public static function error($ex)
    {
        $result["success"] = false;
        if (!empty($ex->getErrorMessage()) && !is_null($ex->getErrorMessage())) {
            $result["message"] = $ex->getErrorMessage();
        }

        $result["error_code"] = $ex->getErrorCode();
        return response()->json($result, $ex->getErrorCode());
    }
}
