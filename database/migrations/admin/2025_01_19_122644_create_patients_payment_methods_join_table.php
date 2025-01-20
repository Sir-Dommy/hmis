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
        Schema::create('patients_payment_methods_join', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('payment_type_id');
            $table->timestamps();

            $table->foreign('patient_id')
                    ->references('id')
                    ->on('patients')
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
        Schema::dropIfExists('patients_payment_methods_join');
    }
};
