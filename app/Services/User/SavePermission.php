<?php

namespace App\Services\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\CoreService\CoreException;
use App\CoreService\CoreService;


class SavePermission extends CoreService
{

    public $transaction = true;
    public $task = "super-admin";

    public function prepare($input)
    {
        $roleList = collect(DB::select("SELECT id, role_code FROM roles
        ORDER BY id ASC"));
        $input["roles"] = [];
        foreach ($roleList as $role) {
            $input["roles"][$role->role_code] = $role->id;
        }
        return $input;
    }

    public function process($input, $originalInput)
    {

        foreach ($input["permissions"] as $permission) {

            $taskId = $permission["task_id"];
            DB::statement("DELETE FROM role_task WHERE task_id = ?", [$taskId]);
            $permissionInput = [];
            foreach ($permission as $key => $value) {
                if (isset($input["roles"][$key]) && $value == "Y")
                    $permissionInput[] = [
                        "task_id" => $taskId,
                        "role_id" =>  $input["roles"][$key]
                    ];
            }

            DB::table("role_task")->insert($permissionInput);
        }
        return [
            "d" => $permissionInput,
            "message" => "Berhasil"
        ];
    }

    protected function validation()
    {
        return [];
    }
}
