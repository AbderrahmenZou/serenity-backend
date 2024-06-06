<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAdvisersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('advisers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('avatar')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('specialty')->nullable();
            $table->string('ID_card')->nullable();
            $table->float('points')->default(100);
            $table->date('age')->nullable();
            $table->string('username')->unique()->nullable();
            $table->integer('Phone_number')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->float('rate')->default(0.0);
            $table->string('bio')->nullable();
            $table->string('role')->default('adviser');
            $table->string('specialities')->nullable();
            $table->string('description')->nullable();
            $table->string('downloading_a_file')->nullable();
            $table->timestamps();
        });

        DB::unprepared('
        CREATE TRIGGER copy_user_info_to_adviser
        AFTER INSERT ON users
        FOR EACH ROW
        BEGIN
            IF NEW.role = "adviser" THEN
                INSERT INTO advisers (id_user, first_name, last_name, avatar, email, password, ID_card, points, age, gender, role, specialities, description, downloading_a_file, created_at, updated_at)
                VALUES (NEW.id, NEW.first_name, NEW.last_name, NEW.avatar, NEW.email, NEW.password, NEW.ID_card, 100, NEW.age, NEW.gender, NEW.role, NEW.specialities, NEW.description, NEW.downloading_a_file, NOW(), NOW());
            END IF;
        END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advisers');
    }
}
