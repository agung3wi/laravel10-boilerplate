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


class EditUser extends CoreService
{

    public $transaction = true;
    public $task = 'super-admin';

    public function prepare($input)
    {
        $model = 'users';
        $classModel = "\\App\\Models\\" . Str::ucfirst(Str::camel($model));
        if (!class_exists($classModel))
            throw new CoreException("Model " . $model . " Not found Coy", 404);

        $input["class_model"] = $classModel;

        return $input;
        // $input["old_data"] = $user;
    }

    public function process($input, $originalInput)
    {
        $classModel = $input["class_model"];
        $object = $classModel::find($input["id"]);
        if (is_null($object)) {
            throw new CoreException("Pengguna tidak ditemukan");
        }

        // START MOVE FILE

        foreach ($classModel::FIELD_UPLOAD as $item) {
            if ($object->{$item} !== $input[$item]) {
                $tmpPath = $input[$item] ?? null;

                if (!is_null($tmpPath)) {
                    if (!Storage::exists($tmpPath)) {
                        throw new CoreException(__("message.tempFileNotFound", ['field' => $item]));
                    }
                    $tmpPath = $input[$item] ?? null;

                    $originalname = pathinfo(storage_path($tmpPath), PATHINFO_FILENAME);
                    $ext = pathinfo(storage_path($tmpPath), PATHINFO_EXTENSION);

                    $newPath = $classModel::FILEROOT . "/" . $originalname . "." . $ext;
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
                    //OLD FILE DELETE
                    $oldFilePath = $object->{$item};
                    Storage::delete($oldFilePath);
                    //END MOVE FILE
                    $object->{$item} = $newPath;
                    Storage::move($tmpPath, $newPath);
                    //END MOVE FILE
                } else {
                    //OLD FILE DELETE
                    $oldFilePath = $object->{$item};
                    Storage::delete($oldFilePath);
                    //END MOVE FILE
                }
            }
        }
        // END MOVE FILE

        foreach ($classModel::FIELD_EDIT as $item) {
            if ($item == "updated_by") {
                $input[$item] = Auth::id();
            }
            if ($item == "password") {
                $input[$item] = password_hash($input["password"], PASSWORD_BCRYPT);
            }
            if (!in_array($item, $classModel::FIELD_UPLOAD)) {
                $object->{$item} = $input[$item];
            }
        }
        $object->save();

        // UNTUK FORMAT DATA IMG
        if (!empty($classModel::FIELD_UPLOAD)) {
            foreach ($classModel::FIELD_UPLOAD as $item) {
                if (preg_match("/file_/i", $item) or preg_match("/img_/i", $item)) {

                    $url = URL::to('api/file/' . $classModel::TABLE . '/' . $item . '/' . $object->id);
                    $filename = pathinfo(storage_path($object->$item), PATHINFO_BASENAME);
                    $ext = pathinfo($object->$item, PATHINFO_EXTENSION);
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
            "message" => __("message.succesfullyUpdate")
        ];
    }

    protected function validation()
    {
        return [
            "email" => "email|nullable",
            "fullname" => "required",
            "role_id" => "required"
        ];
    }
}
