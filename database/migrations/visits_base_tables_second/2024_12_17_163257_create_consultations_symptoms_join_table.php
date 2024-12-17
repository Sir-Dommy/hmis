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
        Schema::create('consultations_symptoms_join', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultation_id');
            $table->unsignedBigInteger('symptom_id');
            $table->timestamps();

            $table->foreign('consultation_id') // Column name
                  ->references('id') // Target column in the parent table
                  ->on('consultations') // Parent table
                  ->onDelete('cascade');

            $table->foreign('symptom_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('symptoms') // Parent table
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations_symptoms_join');
    }
};
