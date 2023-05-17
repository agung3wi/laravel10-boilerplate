<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\CoreService\CoreException;
use App\CoreService\CoreService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Me extends CoreService
{

    public $transaction = false;
    public $task = null;

    public function prepare($input)
    {
        return $input;
    }

    public function process($input, $originalInput)
    {
        $user = Auth::user();
        if (is_null($user)) {
            throw new CoreException(__("message.403"), 403);
        }
        $sql = "SELECT B.task_code FROM role_task A
            INNER JOIN tasks B ON B.id = A.task_id
            INNER JOIN users C ON C.role_id = A.role_id AND C.id = ?";

        $user->tasks =   array_map(function ($item) {
            return $item->task_code;
        }, DB::select($sql, [$user->id]));

        return $user;
    }

    protected function validation()
    {
        return [];
    }
}
