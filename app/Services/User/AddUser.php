<?php

namespace App\Services\User;

use App\Models\Users;
use Illuminate\Support\Facades\DB;
use App\CoreService\CoreException;
use App\CoreService\CoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;


class AddUser extends CoreService
{

    public $transaction = true;
    public $task = 'super-admin';

    public function prepare($input)
    {
        $model = 'users';
        $classModel = "\\App\\Models\\" . Str::ucfirst(Str::camel($model));
        if (!class_exists($classModel))
            throw new CoreException("Model " . $model . " Not found", 404);

        $input["class_model"] = $classModel;

        $usernameExists = DB::selectOne("SELECT 1 FROM users WHERE username = :username", ["username" => $input["username"]]);
        if (!is_null($usernameExists)) {
            throw new CoreException("User dengan username " . $input["username"] . " sudah ada");
        }


        return $input;
    }

    public function process($input, $originalInput)
    {
        $classModel = $input["class_model"];

        $object = new $classModel;

        // MOVE FILE
        foreach ($classModel::FIELD_UPLOAD as $item) {
            $tmpPath = $input[$item] ?? null;

            if (!is_null($tmpPath)) {
                if (!Storage::exists($tmpPath)) {
                    throw new CoreException(__("message.tempFileNotFound", ['field' => $item]));
                }
                $tmpPath = $input[$item];
                $originalname = pathinfo(storage_path($tmpPath), PATHINFO_FILENAME);
                $ext = pathinfo(storage_path($tmpPath), PATHINFO_EXTENSION);

                $newPath = $classModel::FILEROOT . "/" . $originalname . "." . $ext;
                //START MOVE FILE
                if (Storage::exists($newPath)) {

                    $id = 1;
                    $filename = pathinfo(storage_path($newPath), PATHINFO_FILENAME);
                    $ext = pathinfo(storage_path($newPath), PATHINFO_EXTENSION);
                    while (true) {
                        $originalname = $filename . "($id)." . $ext;
                        if (!Storage::exists($classModel::FILEROOT . "/" . $originalname))
                            break;
                        $id++;
                    }
                    $newPath = $classModel::FILEROOT . "/" . $originalname;
                }

                $ext = pathinfo(storage_path($newPath), PATHINFO_EXTENSION);
                $object->{$item} = $newPath;
                Storage::move($tmpPath, $newPath);
                //END MOVE FILE
            }
        }
        foreach ($classModel::FIELD_ADD as $item) {
            if ($item == "password") {
                $input[$item] = password_hash($input["password"], PASSWORD_BCRYPT);
            }
            if (!in_array($item, $classModel::FIELD_UPLOAD)) {
                $inputValue = $input[$item] ?? $classModel::FIELD_DEFAULT_VALUE[$item];
                $object->{$item} = ($inputValue != '') ? $inputValue : null;
            }
        }


        $object->save();


        $classModel::afterInsert($object, $input);

        // UNTUK FORMAT DATA IMG
        if (!empty($classModel::FIELD_UPLOAD)) {
            foreach ($classModel::FIELD_UPLOAD as $item) {
                if (preg_match("/file_/i", $item) or preg_match("/img_/i", $item)) {

                    $url = URL::to('api/file/' . $classModel::TABLE . '/' . $item . '/' . $object->id);
                    $ext = pathinfo($object->$item, PATHINFO_EXTENSION);
                    $filename = pathinfo(storage_path($object->$item), PATHINFO_BASENAME);
                    $object->$item = (object) [
                        "ext" => $ext,
                        "url" => $url,
                        "filename" => $filename,
                        "field_value" => $object->$item
                    ];
                }
            }
        }
        return [
            "data" => $object,
            "message" => __("message.successfullyAdd")
        ];
    }

    protected function validation()
    {
        return [
            "username" => "required|max:25",
            "password" => "required",
            "email" => "email|nullable",
            "fullname" => "required",
            "role_id" => "required"
        ];
    }
}
