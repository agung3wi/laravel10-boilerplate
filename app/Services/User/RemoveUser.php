<?php

namespace App\Services\User;

use App\Models\Users;
use Illuminate\Support\Facades\DB;
use App\CoreService\CoreException;
use App\CoreService\CoreService;


class RemoveUser extends CoreService
{

    public $transaction = true;
    public $task = 'super-admin';

    public function prepare($input)
    {
        $user = Users::find($input["id"]);
        if (is_null($user)) {
            throw new CoreException("Pengguna tidak ditemukan");
        }

        $input["user"] = $user;
        return $input;
    }

    public function process($input, $originalInput)
    {

        $input["user"]->active = "0";
        $input["user"]->save();

        return [
            "data" => $input["user"],
            "message" => __("message.successfullyRemoveUser")
        ];
    }

    protected function validation()
    {
        return [
            "id" => "required|integer"
        ];
    }
}
