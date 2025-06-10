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
        Schema::create('employee_department_join', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('department_id');
            $table->timestamps();

            $table->foreign('employee_id') // Column name
                    ->references('id') // Target column in the parent table
                    ->on('employees') // employees table
                    ->onDelete('cascade');

            $table->foreign('department_id') // Column name
                    ->references('id') // Target column in the parent table
                    ->on('departments') // departments table
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_department_join');
    }
};
