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
        Schema::create('visit_patient_payment_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visit_id');
            $table->unsignedBigInteger('payment_type_id');
            $table->dateTime('time_in')->nullable();
            $table->dateTime('time_out')->nullable();
            $table->timestamps();

            $table->foreign('visit_id')
                    ->references('id')
                    ->on('visits')
                    ->onDelete('cascade');

            
            $table->foreign('payment_type_id')
                    ->references('id')
                    ->on('payment_types')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_patient_payment_types');
    }
};
