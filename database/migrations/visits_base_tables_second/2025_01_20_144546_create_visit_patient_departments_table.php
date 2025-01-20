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
        Schema::create('visit_patient_departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visit_id');
            $table->unsignedBigInteger('department_id');
            $table->dateTime('time_in')->nullable();
            $table->dateTime('time_out')->nullable();
            $table->timestamps();

            $table->foreign('visit_id')
                    ->references('id')
                    ->on('visits')
                    ->onDelete('cascade');

            $table->foreign('department_id')
                    ->references('id')
                    ->on('departments')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_patient_departments');
    }
};
