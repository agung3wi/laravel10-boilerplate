<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\CoreService\CoreException;
use App\CoreService\CoreService;
use Illuminate\Support\Facades\URL;

class GetUser extends CoreService
{

    public $transaction = false;
    public $task = "super-admin";

    public function prepare($input)
    {
        if (is_blank($input, "limit"))
            $input["limit"] = 10000;
        if (is_blank($input, "offset"))
            $input["offset"] = 0;

        $orderList = ['updated_at', 'role_id', 'username', 'name'];
        $sortList = ["ASC", "DESC"];
        if (is_blank($input, "order"))
            $input["order"] = "updated_at";

        if (is_blank($input, "sort"))
            $input["sort"] = "DESC";

        if (in_array($input["order"], $orderList))
            $input["order"] = "A.updated_at";

        if (in_array(strtoupper($input["sort"]), $sortList))
            $input["sort"] = $input["sort"];

        return $input;
    }

    public function process($input, $originalInput)
    {
        $params = [];
        $condition = "WHERE true AND A.id != 1 ";

        if (!is_blank($input, "search")) {
            $condition = $condition . " AND (";
            $condition = $condition . " A.username ILIKE :search";
            $condition = $condition . " OR A.fullname ILIKE :search";
            $condition = $condition . " OR A.email ILIKE :search";
            $condition = $condition . " OR A.telephone ILIKE :search";
            $condition = $condition . ")";
            $params["search"] = "%" . $input['search'] . "%";
        }

        if (!is_blank($input, "active")) {
            $condition = $condition . " AND A.active = :active";
            $params["active"] = $input["active"];
        };

        if (!is_blank($input, "role_id")) {
            $condition = $condition . " AND A.role_id = :role_id";
            $params["role_id"] = $input["role_id"];
        };
        $total = DB::selectOne("SELECT COUNT(1) AS total
            FROM users A " .
            $condition, $params)->total;

        $sql = "SELECT A.*, null AS password, B.role_name AS rel_role_id
                FROM users A
            LEFT JOIN roles B ON B.id = A.role_id
            $condition
            ORDER BY " . $input['order'] . " " . $input['sort'] . " LIMIT :limit OFFSET :offset";

        $params["limit"] = $input["limit"];
        $params["offset"] = $input["offset"];

        $userList = DB::select($sql, $params);
        array_map(function ($key) {
            foreach ($key as $field => $value) {
                if (preg_match("/file_/i", $field) or preg_match("/img_/i", $field)) {
                    $url = URL::to('api/file/users/' . $field . '/' . $key->id);
                    $tumbnailUrl = URL::to('api/tumb-file/users/' . $field . '/' . $key->id);
                    $filename = pathinfo(storage_path($value), PATHINFO_FILENAME);
                    $ext = pathinfo($value, PATHINFO_EXTENSION);
                    $key->$field = (object) [
                        "ext" => $ext,
                        "url" => $url,
                        "tumbnail_url" => $tumbnailUrl,
                        "filename" => $filename,
                        "field_value" => $key->$field
                    ];
                }
            }
            return $key;
        }, $userList);
        return [
            "data" => $userList,
            "total" => $total,
        ];
    }

    protected function validation()
    {
        return [
            "limit" => "integer|min:0|max:1000",
            "offset" => "integer|min:0",
            "branch_id" => "integer"
        ];
    }
}
