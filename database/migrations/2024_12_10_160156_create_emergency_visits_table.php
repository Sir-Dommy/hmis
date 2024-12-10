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
        Schema::create('emergency_visits', function (Blueprint $table) {
            $table->id();
            $table->string('patient_type');
            $table->string('patient_name');
            $table->string('gender');
            $table->integer('age');
            $table->unsignedBigInteger('payment_type_id')->nullable();
            $table->string('contact_info');
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();            
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('payment_type_id')
                    ->references('id')
                    ->on('payment_types')
                    ->onDelete('cascade');

            $table->foreign('clinic_id')
                    ->references('id')
                    ->on('clinics')
                    ->onDelete('cascade');

            $table->foreign('doctor_id')
                    ->references('id')
                    ->on('doctors')
                    ->onDelete('cascade');

            $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

            $table->foreign('updated_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

            $table->foreign('deleted_by')
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
        Schema::dropIfExists('emergency_visits');
    }
};
