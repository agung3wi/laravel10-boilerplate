<?php

namespace App\Services\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\CoreService\CoreException;
use App\CoreService\CoreService;
use App\Models\Role;

class FindRoleById extends CoreService
{

    public $transaction = false;
    public $task = "super-admin";

    public function prepare($input)
    {
        return $input;
    }

    public function process($input, $originalInput)
    {
        $user = Role::find($input["id"]);
        if (empty($user))
            throw New CoreException(__("message.dataNotFound"), 404);

        return $user;
    }

    protected function validation()
    {
        return [
            "id" => "required|integer"
        ];
    }
}
