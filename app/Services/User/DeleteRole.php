<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\CoreService\CoreException;
use App\CoreService\CoreService;
use App\Models\Role;

class DeleteRole extends CoreService
{

    public $transaction = true;
    public $task = 'super-admin';

    public function prepare($input)
    {
        $role = Role::find($input["id"]);
        if (is_null($role)) {
            throw new CoreException("Role dengan id " . $input["id"] . " tidak ditemukan");
        }
        $input["role"] = $role;
        return $input;
    }

    public function process($input, $originalInput)
    {

        $input["role"]->delete();

        return [
            "data" => $input["role"], 
            "message" => __("message.successfullyDelete")
        ];
    }

    protected function validation()
    {
        return [
            "id" => "required|integer"
        ];
    }
}
