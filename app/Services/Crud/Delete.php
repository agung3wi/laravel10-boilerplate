<?php

namespace App\Services\Crud;

use App\CoreService\CoreException;
use App\CoreService\CoreService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Delete extends CoreService
{

    public $transaction = true;
    public $task = null;

    public function prepare($input)
    {
        $model = $input["model"];
        $classModel = "\\App\\Models\\" . Str::ucfirst(Str::camel($model));
        if (!class_exists($classModel))
            throw new CoreException(__("message.model404", ['model' => $model]), 404);

        if (!$classModel::IS_DELETE)
            throw new CoreException("Not found", 404);

        if (!hasPermission("delete-" . $model))
            throw new CoreException("Forbidden", 403);
        $input["class_model"] = $classModel;
        return $input;
    }

    public function process($input, $originalInput)
    {
        $classModel = $input["class_model"];
        $object = $classModel::find($input["id"]);
        if (!$object) {
            throw new CoreException(__("message.dataNotFound", ['id' => $input["id"]]));
        }
        $rules = ["id" => "required|integer"];

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            throw new CoreException($validator->errors()->first());
        }



        try {
            //PERTAMA DELETE DULU FILENYA
            foreach ($classModel::FIELD_UPLOAD as $item) {
                $path = $object->{$item};
                Storage::delete($path);
            }
            //THEN DELETE CHILD FILES

            if (!empty($classModel::CHILD_TABLE))

                foreach ($classModel::CHILD_TABLE as $item => $value) {
                    $childClassModel = "\\App\\Models\\" . Str::ucfirst(Str::camel($item));
                    $childObject = $childClassModel::where($value["foreignField"], $object->id)->first();
                    foreach ($childClassModel::FIELD_UPLOAD as $item) {
                        $childPath = $childObject->{$item};
                        Storage::delete($childPath);
                    }
                    //
                    if (!empty($childClassModel::CHILD_TABLE))
                        foreach ($childClassModel::CHILD_TABLE as $childTtem => $childValue) {
                            $childClassModeln = "\\App\\Models\\" . Str::ucfirst(Str::camel($childTtem));
                            $childObjectn = $childClassModeln::where($childValue["foreignField"], $childObject->id)->first();
                            // $childObjectn->delete();
                            foreach ($childClassModeln::FIELD_UPLOAD as $itemn) {
                                $childPathn = $childObjectn->{$itemn};
                                Storage::delete($childPathn);
                            }
                        }
                }
            $object->delete();
        } catch (QueryException $ex) {
            throw new CoreException(__("message.forbiddenDelete"));
        }

        $classModel::afterDelete($object, $input);

        return [
            "data" => $object,
            "message" => __("message.successfullyDelete")
        ];
    }

    protected function validation()
    {
        return [];
    }
}
