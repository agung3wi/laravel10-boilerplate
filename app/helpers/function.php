<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\CoreService\CoreException;
use Illuminate\Support\Carbon;

if (!function_exists('hasPermission')) {
    function hasPermission($task)
    {
        $user = Auth::user();
        if ($user) {
            $permission = DB::selectOne("SELECT B.role_id FROM users A
            INNER JOIN role_task B ON B.role_id = A.role_id
            INNER JOIN tasks C ON B.task_id = C.id AND C.task_code = ?
            WHERE A.id = ?", [$task, $user->id]);
            return !is_null($permission) ? true : ($user->role_id == 1);
        } else {
            return false;
        }
    }
}

if (!function_exists('isProduction')) {
    function isProduction()
    {
        return env("APP_ENV") == "production" || env("APP_ENV") == "staging";
    }
}

if (!function_exists('is_blank')) {

    function is_blank($array, $key)
    {
        return isset($array[$key]) ? (is_null($array[$key]) || $array[$key] === "") : true;
    }
}

if (!function_exists('toAlpha')) {

    function toAlpha($data)
    {
        $alphabet =   array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L',
            'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
        );
        return $alphabet[$data];
    }
}

if (!function_exists('arrayToString')) {

    function arrayToString($array)
    {
        $list = [];
        foreach ($array as $value) {
            if (is_array($value))
                $list[] = arrayToString($value);
            else
                $list[] = '"' . $value . '"';
        }
        return "[" . implode(", ", $list) . "]";
    }
}

if (!function_exists('get_string_between')) {

    function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}

if (!function_exists('custom_get_origin_field_upload')) {

    function custom_get_origin_field_upload($string)
    {
        $a = substr($string, 0, 4);

        $newString = preg_replace('/' . $a . '/', '', $string, 1);
        $b = substr($newString, 0, 1);
        if ($b == '_') {
            $newString = preg_replace('/[_]/', '', $newString, 1);
        }
        return $newString;
    }
}
