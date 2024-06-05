<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_become_advisers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBecomeAdvisersTable extends Migration
{
    public function up()
    {
        Schema::create('become_advisers', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique()->nullable();
            $table->string('first_name')->default('John');
            $table->string('last_name')->default('Doe'); 
            $table->string('avatar')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->date('age')->nullable();
            $table->integer('Phone_number')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('role')->default('adviser');
            $table->string('specialities')->nullable();
            $table->string('description')->nullable();
            $table->string('downloading_a_file')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('become_advisers');
    }
}
