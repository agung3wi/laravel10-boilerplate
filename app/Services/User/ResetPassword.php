<?php

namespace App\Services\User;

use App\Service\LogActivity;
use App\CoreService\CoreException;
use App\CoreService\CoreService;
use App\CoreService\CallService;
use App\Models\User;

class ResetPassword extends CoreService
{

    public $transaction = true;
    public $task = 'super-admin';

    public function prepare($input)
    {
        $input["user_detail"] = User::find($input["id"]);
        return $input;
    }

    public function process($input, $originalInput)
    {
        $password = rand(100000, 999999);
        $input["user_detail"]->password = password_hash($password, PASSWORD_BCRYPT);
        $input["user_detail"]->updated_at = $input["session"]["datetime"];
        $input["user_detail"]->save();

        return [
            "message" => "Berhasil Mereset Password. Password Baru Anda Adalah " . $password . ".
            Ganti Password Anda demi keamanan!"
        ];
    }

    protected function validation()
    {
        return [
            "id" => "required"
        ];
    }
}
