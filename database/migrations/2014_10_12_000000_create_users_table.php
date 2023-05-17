<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fullname');
            $table->string('username')->unique();
            $table->text('password');
            $table->foreignId('role_id')->constrained('roles');
        });

        $role = DB::selectOne("SELECT id FROM roles WHERE role_code = 'super-admin'");
        DB::table("users")->insert([
            "fullname" => "Super Admin",
            "username" => "admin",
            "password" => bcrypt("admin"),
            "role_id" => $role->id
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
