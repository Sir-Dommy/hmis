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
        Schema::create('patients_chronic_diseases_join', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('chronic_disease_id');
            $table->timestamps();

            $table->foreign('patient_id') // Column name
                  ->references('id') // Target column in the parent table
                  ->on('patients') // Parent table
                  ->onDelete('cascade');

            $table->foreign('chronic_disease_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('chronic_diseases') // Parent table
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations_patients_chronic_diseases_join');
    }
};
