<?php

return [
    [
        "type" => "GET",
        "end_point" => "/get",
        "class" => "App\Services\Crud\Get"
    ],
    [
        "type" => "GET",
        "end_point" => "/dataset",
        "class" => "App\Services\Crud\Dataset"
    ],
    [
        "type" => "POST",
        "end_point" => "/create",
        "class" => "App\Services\Crud\Add"
    ],
    [
        "type" => "POST",
        "end_point" => "/update",
        "class" => "App\Services\Crud\Edit"
    ],
    [
        "type" => "POST",
        "end_point" => "/delete",
        "class" => "App\Services\Crud\Delete"
    ],
    [
        "type" => "GET",
        "end_point" => "/show",
        "class" => "App\Services\Crud\Find"
    ],
    [
        "type" => "GET",
        "end_point" => "/sample",
        "class" => "App\Services\Sample\SampleService"
    ],
    [
        "type" => "GET",
        "end_point" => "/sample",
        "class" => "App\Services\Sample\SampleService"
    ],
    [
        "type" => "GET",
        "end_point" => "/me",
        "class" => "App\Services\Auth\Me"
    ],
    [
        "type" => "GET",
        "end_point" => "/logout",
        "class" => "App\Services\Auth\DoLogout"
    ],
    [
        "type" => "POST",
        "end_point" => "/login",
        "class" => "App\Services\Auth\DoLogin"
    ],
    // [
    //     "type" => "POST",
    //     "end_point" => "/register",
    //     "class" => "App\Services\Auth\DoRegister"
    // ],
    // [
    //     "type" => "POST",
    //     "end_point" => "/request_forgot_password",
    //     "class" => "App\Services\Auth\DoRequestForgotPassword"
    // ],
    // [
    //     "type" => "POST",
    //     "end_point" => "/change_password",
    //     "class" => "App\Services\Auth\DoChangePassword"
    // ],
    // [
    //     "type" => "GET",
    //     "end_point" => "/users/show",
    //     "class" => "App\Services\User\FindUserById"
    // ],
    // [
    //     "type" => "GET",
    //     "end_point" => "/custom/users/findusername",
    //     "class" => "App\Services\User\FindUserByUsername"
    // ],
    [
        "type" => "GET",
        "end_point" => "/users/list",
        "class" => "App\Services\User\GetUser"
    ],
    [
        "type" => "POST",
        "end_point" => "/users/create",
        "class" => "App\Services\User\AddUser"
    ],
    [
        "type" => "PUT",
        "end_point" => "/users/update",
        "class" => "App\Services\User\EditUser"
    ],
    [
        "type" => "POST",
        "end_point" => "/users/remove",
        "class" => "App\Services\User\RemoveUser"
    ],
    [
        "type" => "POST",
        "end_point" => "/users/restore",
        "class" => "App\Services\User\RestoreUser"
    ],
    [
        "type" => "POST",
        "end_point" => "/reset-password",
        "class" => "App\Services\User\ResetPassword"
    ],
    [
        "type" => "GET",
        "end_point" => "/permission",
        "class" => "App\Services\User\ViewPermission"
    ],
    [
        "type" => "POST",
        "end_point" => "/permission/save",
        "class" => "App\Services\User\SavePermission"
    ],
    [
        "type" => "GET",
        "end_point" => "/roles",
        "class" => "App\Services\User\GetRole"
    ],
    [
        "type" => "POST",
        "end_point" => "/roles/create",
        "class" => "App\Services\User\AddRole"
    ],
    [
        "type" => "PUT",
        "end_point" => "/roles/update",
        "class" => "App\Services\User\EditRole"
    ],
    [
        "type" => "DELETE",
        "end_point" => "/roles/delete",
        "class" => "App\Services\User\DeleteRole"
    ],
    [
        "type" => "GET",
        "end_point" => "/role/find",
        "class" => "App\Services\User\FindRoleById"
    ],

];
