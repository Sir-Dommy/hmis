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
        Schema::create('visit_patient_insurance_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visit_id');
            $table->string('claim_number');
            $table->double('available_balance', 8, 2)->nullable();
            $table->unsignedBigInteger('scheme_id');
            $table->string('document_path')->nullable();
            $table->timestamps();

            $table->foreign('visit_id')
                    ->references('id')
                    ->on('visits')
                    ->onDelete('cascade');

            $table->foreign('scheme_id')
                    ->references('id')
                    ->on('schemes')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_patient_insurance_details');
    }
};
