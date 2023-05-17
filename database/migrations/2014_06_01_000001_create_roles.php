<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('role_code', 255)->unique();
            $table->string('role_name', 255);
            $table->text('description');
        });

        DB::table("roles")->insert([
            "role_code" => "super-admin",
            "role_name" => "Super Admin",
            "description" => "Super Admin"
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
