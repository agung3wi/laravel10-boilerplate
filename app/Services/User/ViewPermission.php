<?php

namespace App\Services\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\CoreService\CoreException;
use App\CoreService\CoreService;
use Illuminate\Support\Str;

class ViewPermission extends CoreService
{

    public $transaction = false;
    public $task = "super-admin";

    public function prepare($input)
    {
        return $input;
    }

    public function process($input, $originalInput)
    {
        $condition = "WHERE true";
        $params = [];

        if (!is_blank($input, "search")) {

            $searchableList = ["task_code", "task_name", "description"];

            $searchableList = array_map(function ($item) {
                return "UPPER($item) ILIKE :search";
            }, $searchableList);
        } else {
            $searchableList = [];
        }



        if (count($searchableList) > 0 && !is_blank($input, "search"))
            $params["search"] = "%" . strtoupper($input["search"] ?? "") . "%";

        $sql = "SELECT * FROM tasks " . $condition . " " . (count($searchableList) > 0 ? " AND (" . implode(" OR ", $searchableList) . ")" : "");

        $tasks = DB::select($sql, $params);
        $roles = DB::select("SELECT * FROM roles");
        $roleTaskList = [];
        foreach (DB::select("SELECT * FROM role_task") as $roleTask) {
            $roleTaskList[$roleTask->role_id][$roleTask->task_id] = true;
        }
        $result = [];
        $i = 0;

        foreach ($tasks as $task) {
            $result[$i] = [
                "task_id" => $task->id,
                "task_group" => $task->task_group,
                "task_code" => $task->task_code,
                "task_name" => $task->task_name,
                // "roles" => []
            ];
            foreach ($roles as $role) {
                // $d = [
                //     $role->role_code => isset($roleTaskList[$role->id][$task->id]) ? "Y" : "N"
                // ];


                // array_push($result[$i]["roles"], $d);
                $result[$i][$role->role_code] =
                    isset($roleTaskList[$role->id][$task->id]) ? "Y" : "N";
            }
            $i++;
        }

        return $result;
    }

    protected function validation()
    {
        return [];
    }
}
