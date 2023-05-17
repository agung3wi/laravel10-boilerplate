<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\CoreService\CoreException;
use App\CoreService\CoreService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class DoLogin extends CoreService
{

    public $transaction = false;
    public $task = null;

    public function prepare($input)
    {


        return $input;
    }

    public function process($input, $originalInput)
    {

        $credentials = [
            "username" => $input["username"],
            "password" => $input["password"]
        ];

        $user = User::select("users.*", "roles.role_name")->leftjoin('roles', 'roles.id', 'users.role_id')->where("username", $input["username"])->first();
        if (empty($user))
            throw new CoreException(__("message.userNotFound", ['username' => $input["username"]]), 422);

        if (Config::get("auth.defaults.guard") == "web") {
            if ($token = !Auth::attempt($credentials)) {
                throw new CoreException(__("message.loginCredentialFalse"), 401);
            }
            Auth::loginUsingId($user->id);
            $token = null;
        } else {
            if ($token = Auth::attempt($credentials)) {
            } else {
                throw new CoreException(__("message.loginCredentialFalse"), 401);
            }
        }

        $sql = "SELECT B.task_code FROM role_task A
            INNER JOIN tasks B ON B.id = A.task_id
            INNER JOIN users C ON C.role_id = A.role_id AND C.id = ?";

        $permissionList =  array_map(function ($item) {
            return $item->task_code;
        }, DB::select($sql, [$user->id]));


        return [
            "user" => $user,
            "token" => $token,
            "tasks" => $permissionList,
            "message" => __("message.loginSuccess")
        ];
    }

    protected function validation()
    {
        return [
            "username" => "required",
            "password" => "required"
        ];
    }
}
