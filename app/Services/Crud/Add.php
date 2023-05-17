<?php

namespace App\Services\Crud;

use App\CoreService\CallService;
use App\CoreService\CoreException;
use App\CoreService\CoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class Add extends CoreService
{

    public $transaction = true;
    public $task = null;

    public function prepare($input)
    {
        $model = $input["model"];
        $classModel = "\\App\\Models\\" . Str::ucfirst(Str::camel($model));
        if (!class_exists($classModel))
            throw new CoreException(__("message.model404", ['model' => $model]), 404);

        if (!$classModel::IS_ADD)
            throw new CoreException("Not found", 404);

        if (!hasPermission("create-" . $model))
            throw new CoreException("Forbidden", 403);
        $input["class_model"] = $classModel;

        if ($classModel::FIELD_ARRAY) {
            foreach ($classModel::FIELD_ARRAY as $item) {
                $input[$item] = serialize($input[$item]);
            }
        }

        if ($classModel::FIELD_UPLOAD) {
            foreach ($classModel::FIELD_UPLOAD as $item) {
                if (isset($input[$item])) {
                    if (is_array($input[$item])) {
                        $input[$item] = isset($input[$item]["path"]) ? $input[$item]["path"] : $input[$item]["field_value"];
                    }
                }
            }
        }

        $validator = Validator::make($input, $classModel::FIELD_VALIDATION);

        if ($validator->fails()) {
            throw new CoreException($validator->errors()->first());
        }

        if ($classModel::FIELD_UNIQUE) {
            foreach ($classModel::FIELD_UNIQUE as $search) {
                $query = $classModel::whereRaw("true");
                $fieldTrans = [];
                foreach ($search as $key) {
                    $fieldTrans[] = __("field.$key");
                    $query->where($key, $input[$key]);
                };
                $isi = $query->first();
                if (!is_null($isi)) {
                    throw new CoreException(__("message.alreadyExist", ['field' => implode(",", $fieldTrans)]));
                }
            }
        }
        return $input;
    }

    public function process($input, $originalInput)
    {

        $response = [];
        $classModel = $input["class_model"];

        $input = $classModel::beforeInsert($input);

        $object = new $classModel;
        foreach ($classModel::FIELD_ADD as $item) {
            if ($item == "created_by") {
                $input[$item] = Auth::id();
            }
            if ($item == "updated_by") {
                $input[$item] = Auth::id();
            }
            if (isset($input[$item])) {
                $inputValue = $input[$item] ?? $classModel::FIELD_DEFAULT_VALUE[$item];
                $object->{$item} = ($inputValue !== '') ? $inputValue : null;
            }
        }


        $object->save();



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
            }
        }
        //END MOVE FILE



        $object->save();

        $displayedDataAfterInsert["id"] = $object->id;
        $displayedDataAfterInsert["model"] = $classModel::TABLE;

        $displayedDataAfterInsert = CallService::run("Find", $displayedDataAfterInsert);

        // $displayedDataAfterInsert = $displayedDataAfterInsert->original["data"];
        //AFTER INSERT
        // $afterInsertedRespnese = $classModel::afterInsert($displayedDataAfterInsert, $input);

        $response["data"] = $object;
        // $response["after_inserted_response"] = $afterInsertedRespnese;
        // $response["message"] = __("message.successfullyAdd");

        return $response;
    }

    protected function validation()
    {
        return [];
    }
}
