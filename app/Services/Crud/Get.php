<?php

namespace App\Services\Crud;

use App\CoreService\CoreException;
use App\CoreService\CoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class Get extends CoreService
{

    public $transaction = false;
    public $task = null;

    public function prepare($input)
    {
        $model = $input["model"];
        $classModel = "\\App\\Models\\" . Str::ucfirst(Str::camel($model));
        if (!class_exists($classModel))
            throw new CoreException(__("message.model404", ['model' => $model]), 404);

        if (!$classModel::IS_LIST)
            throw new CoreException(__("message.404"), 404);

        if (!hasPermission("view-" . $model))
            throw new CoreException(__("message.403"), 403);
        $input["class_model_name"] = $model;
        $input["class_model"] = $classModel;
        return $input;
    }

    public function process($input, $originalInput)
    {
        $classModelName = $input["class_model_name"];
        $classModel = $input["class_model"];

        $selectableList = [];
        $sortBy = $classModel::TABLE . ".id";
        $sort = strtoupper($input["sort"] ?? "DESC") == "ASC" ? "ASC" : "DESC";

        $sortableList = $classModel::FIELD_SORTABLE;

        if (in_array($input["sort_by"] ?? "", $sortableList)) {
            $sortBy = $input["sort_by"];
        }

        $tableJoinList = [];
        $filterList = [];
        $params = [];

        foreach ($classModel::FIELD_LIST as $list) {
            $selectableList[] = $classModel::TABLE . "." . $list;
        }



        foreach ($classModel::FIELD_FILTERABLE as $filter => $operator) {
            if (!is_blank($input, $filter)) {

                $cekTypeInput = json_decode($input[$filter], true);
                if (!is_array($cekTypeInput)) {
                    $filterList[] = " AND " . $classModel::TABLE . "." . $filter .  " " . $operator["operator"] . " :$filter";
                    $params[$filter] = $input[$filter];
                } else {
                    $input[$filter] = json_decode($input[$filter], true);
                    if ($input[$filter]["operator"] == 'between') {

                        $filterList[] = " AND " . $classModel::TABLE . "." . $filter .  " " . $input[$filter]["operator"] . " '" . $input[$filter]["value"][0] . "' AND '" . $input[$filter]["value"][1] . "'";
                    } else {

                        $filterList[] = " AND " . $classModel::TABLE . "." . $filter .  " " . $input[$filter]["operator"] . " :$filter";
                        $params[$filter] = $input[$filter];
                    }
                }

                // if ($operator["operator"] == 'between') {
                //     $filterValue = json_decode($input[$filter]);
                //     $filterList[] = " AND " . $classModel::TABLE . "." . $filter .  " " . $operator["operator"] . " '" . $filterValue[0] . "' AND '" . $filterValue[1] . "'";
                // } else {
                //     $filterList[] = " AND " . $classModel::TABLE . "." . $filter .  " " . $operator["operator"] . " :$filter";
                //     $params[$filter] = $input[$filter];
                // }
            }
        }

        $i = 0;
        foreach ($classModel::FIELD_RELATION as $key => $relation) {
            // $alias = toAlpha($i + 1);
            $alias = $relation["aliasTable"];
            ///
            $fieldDisplayed = "CONCAT_WS (' - ',";
            foreach ($relation["selectFields"] as $keyField) {
                $fieldDisplayed .= $alias . '.' . $keyField . ",";
            }
            $fieldDisplayed = substr($fieldDisplayed, 0, strlen($fieldDisplayed) - 1);
            $fieldDisplayed .= ") AS " . $relation["displayName"];
            $selectableList[] = $fieldDisplayed;
            ///
            // $selectableList[] = $alias . "." . $relation["selectValue"];

            $tableJoinList[] = "LEFT JOIN " . $relation["linkTable"] . " " . $alias . " ON " .
                $classModel::TABLE . "." . $key . " = " .  $alias . "." . $relation["linkField"];
            $i++;
        }

        if (!empty($classModel::CUSTOM_SELECT)) $selectableList[] = $classModel::CUSTOM_SELECT;

        $condition = " WHERE true";

        if (!empty($classModel::CUSTOM_LIST_FILTER)) {
            foreach ($classModel::CUSTOM_LIST_FILTER as $customListFilter) {
                $condition .= " AND " . $customListFilter;
            }
        }
        if (!is_blank($input, "search")) {

            $searchableList = $classModel::FIELD_SEARCHABLE;

            $searchableList = array_map(function ($item) {
                return "UPPER($item) ILIKE :search";
            }, $searchableList);
        } else {
            $searchableList = [];
        }


        if (count($searchableList) > 0 && !is_blank($input, "search"))
            $params["search"] = "%" . strtoupper($input["search"] ?? "") . "%";

        $limit = $input["limit"] ?? 10;
        $offset = $input["offset"] ?? 0;
        if (!is_null($input["page"] ?? null)) {
            $offset = $limit * ($input["page"] - 1);
        }


        // START FILTER MAPPING PROJECT
        // $roleId = Auth::user()->role_id;
        // if($roleId == 2 and $classModel::TABLE == 'departments'){
        //     $tableJoinList[] = "JOIN mapping_users_departments MUD ON " .
        //         $classModel::TABLE . ".id = MUD.department_id";
        //     $condition .= " AND MUD.user_id = " . Auth::id() . "AND MUD.active = 1";
        // }else if ($roleId == 2 and $classModel::TABLE == 'projects') {
        //     $tableJoinList[] = "JOIN mapping_users_departments MUD ON " .
        //         $classModel::TABLE . ".department_id = MUD.department_id";
        //     $condition .= " AND MUD.user_id = " . Auth::id() . "AND MUD.active = 1";
        // } else if ($roleId == 2 and in_array("project_id", $classModel::FIELD_LIST)) {
        //     $tableJoinList[] = "JOIN projects PROJ ON " .
        //         $classModel::TABLE . ".project_id = PROJ.id";
        //     $tableJoinList[] = "JOIN mapping_users_departments MUD ON PROJ.department_id = MUD.department_id";
        //     $condition .= " AND MUD.user_id = " . Auth::id() . "AND MUD.active = 1";
        // } else if ($roleId > 2 and $classModel::TABLE == 'projects') {
        //     $tableJoinList[] = "JOIN mapping_users_projects MUP ON " .
        //         $classModel::TABLE . ".id = MUP.project_id";
        //     $condition .= " AND MUP.user_id = " . Auth::id() . "AND MUP.active = 1";
        // } else if ($roleId > 2 and in_array("project_id", $classModel::FIELD_LIST)) {
        //     $tableJoinList[] = "JOIN mapping_users_projects MUP ON " .
        //         $classModel::TABLE . ".project_id = MUP.project_id";
        //     $condition .= " AND MUP.user_id = " . Auth::id() . "AND MUP.active = 1";
        // }
        // END FILTER MAPPING


        $sql = "SELECT " . implode(", ", $selectableList) . " FROM " . $classModel::TABLE . " " .
            implode(" ", $tableJoinList) . $condition .
            (count($searchableList) > 0 ? " AND (" . implode(" OR ", $searchableList) . ")" : "") .
            implode("\n", $filterList) . " ORDER BY " . $sortBy . " " . $sort . " LIMIT $limit OFFSET $offset ";
        $sqlForCount = "SELECT COUNT(1) AS total FROM " . $classModel::TABLE . " " .
            implode(" ", $tableJoinList) . $condition .
            (count($searchableList) > 0 ? " AND (" . implode(" OR ", $searchableList) . ")" : "") .
            implode("\n", $filterList);

        $object =  DB::select($sql, $params);

        foreach ($classModel::FIELD_ARRAY as $item) {
        }

        array_map(function ($key) use ($classModel, $classModelName) {
            foreach ($key as $field => $value) {
                $key->class_model_name = $classModelName;
                if ((preg_match("/file_/i", $field) or preg_match("/img_/i", $field)) and !is_null($key->$field)) {
                    $url = URL::to('api/file/' . $classModel::TABLE . '/' . $field . '/' . $key->id);
                    $tumbnailUrl = URL::to('api/tumb-file/' . $classModel::TABLE . '/' . $field . '/' . $key->id);
                    $ext = pathinfo($key->$field, PATHINFO_EXTENSION);
                    $filename = pathinfo(storage_path($key->$field), PATHINFO_BASENAME);

                    $key->$field = (object) [
                        "ext" => (is_null($key->$field)) ? null : $ext,
                        "url" => $url,
                        "tumbnail_url" => $tumbnailUrl,
                        "filename" => (is_null($key->$field)) ? null : $filename,
                        "field_value" => $key->$field
                    ];
                }
                if (preg_match("/array_/i", $field)) {
                    $key->$field = unserialize($key->$field);
                    if (!$key->$field) {
                        $key->$field = null;
                    }
                }
            }
            return $key;
        }, $object);


        $total = DB::selectOne($sqlForCount, $params)->total;
        $totalPage = ceil($total / $limit);
        return [
            "data" => $object,
            "total" => $total,
            "totalPage" => $totalPage
        ];
    }

    protected function validation()
    {
        return [];
    }
}
