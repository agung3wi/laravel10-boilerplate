<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InitTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $data = [
            [
                "task_group" => "SUPER ADMIN",
                "task_code" => "super-admin",
                "task_name" => "Super Admin",
                "description" => "Role Untuk Super Admin Bisa Apa Aja"
            ],
            ///////// USERS //////////////
            [
                "task_group" => "USERS",
                "task_code" => "view-users",
                "task_name" => "View Users",
                "description" => "Melihat Data List, Detail, Lookup Users "
            ],
            [
                "task_group" => "USERS",
                "task_code" => "create-users",
                "task_name" => "create Users",
                "description" => "Membuat Data Users "
            ],
            [
                "task_group" => "USERS",
                "task_code" => "update-users",
                "task_name" => "update Users",
                "description" => "Mengubah Data Users "
            ],
            [
                "task_group" => "USERS",
                "task_code" => "delete-users",
                "task_name" => "delete Users",
                "description" => "Menghapus Data Users "
            ],

            ///////// ROLES //////////////
            [
                "task_group" => "ROLES",
                "task_code" => "view-roles",
                "task_name" => "View Roles",
                "description" => "Melihat Data List, Detail, Lookup Roles "
            ],
            [
                "task_group" => "ROLES",
                "task_code" => "create-roles",
                "task_name" => "create Roles",
                "description" => "Membuat Data Roles "
            ],
            [
                "task_group" => "ROLES",
                "task_code" => "update-roles",
                "task_name" => "update Roles",
                "description" => "Mengubah Data Roles "
            ],
            [
                "task_group" => "ROLES",
                "task_code" => "delete-roles",
                "task_name" => "delete Roles",
                "description" => "Menghapus Data Roles "
            ]
        ];
        DB::table('tasks')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
