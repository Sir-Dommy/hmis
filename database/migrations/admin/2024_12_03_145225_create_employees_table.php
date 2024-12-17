<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id(); //primary Key
            $table->string('ipnumber')->unique();
            $table->string('employee_name');
            $table->string('employee_code')->unique();
            $table->integer('age')->nullable();;
            $table->date('dob');
            $table->string('role');
            $table->string('speciality');
            $table->unsignedBigInteger('user_id'); //foreign key from users table
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('disabled_by')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id') //column name
                    ->references('id') // Target column in the parent table
                    ->on('users')  // parent table
                    ->onDelete('cascade');  //operation

            $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

            $table->foreign('approved_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

            $table->foreign('updated_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

            $table->foreign('disabled_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
