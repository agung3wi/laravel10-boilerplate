<?php

namespace App\Services\User;

use App\Model\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\CoreService\CoreException;
use App\CoreService\CoreService;


class GetRole extends CoreService
{

    public $transaction = false;
    public $task = "super-admin";

    public function prepare($input)
    {
        if (is_blank($input, "limit"))
            $input["limit"] = 10000;
        if (is_blank($input, "offset"))
            $input["offset"] = 0;

        $orderList = ['updated_at', 'id', 'role_name', 'role_code'];
        $sortList = ["ASC", "DESC"];
        if (is_blank($input, "order"))
            $input["order"] = "id";

        if (is_blank($input, "sort"))
            $input["sort"] = "ASC";

        if (in_array($input["order"], $orderList))
            $input["order"] = "A.".$input["order"];

        if (in_array(strtoupper($input["sort"]), $sortList))
            $input["sort"] = strtoupper($input["sort"]);

        return $input;
    }

    public function process($input, $originalInput)
    {
        $params = [];
        $condition = "WHERE true";
        if (!is_blank($input, "search")) {
            $condition = $condition . " AND (";
            $condition = $condition . " A.role_name ILIKE :search";
            $condition = $condition . " OR A.role_code ILIKE :search";
            $condition = $condition . ")";
            $params["search"] = "%" . $input['search'] . "%";
        }

        $total = DB::selectOne("SELECT COUNT(1) AS total
            FROM roles A " .
            $condition, $params)->total;


        $sql = "SELECT A.* FROM roles A $condition ORDER BY " . $input['order'] . " " . $input['sort'] . " LIMIT :limit OFFSET :offset";
        $params["limit"] = $input["limit"];
        $params["offset"] = $input["offset"];

        $roleList = DB::select($sql, $params);

        return [
            "data" => $roleList,
            "total" => $total,
        ];
    }

    protected function validation()
    {
        return [];
    }
}
