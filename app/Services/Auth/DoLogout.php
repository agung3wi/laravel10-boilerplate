<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\CoreService\CoreException;
use App\CoreService\CoreService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DoLogout extends CoreService
{

    public $transaction = false;
    public $task = null;

    public function prepare($input)
    {
        return $input;
    }

    public function process($input, $originalInput)
    {
        Auth::logout();
        return [];
    }

    protected function validation()
    {
        return [];
    }
}
