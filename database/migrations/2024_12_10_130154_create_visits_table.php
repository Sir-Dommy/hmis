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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->string('claim_number');
            $table->double('amount', 8, 2);
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('visit_type');
            $table->unsignedBigInteger('scheme_id')->nullable();
            $table->unsignedBigInteger('fee_type')->nullable();
            $table->string('stage');
            $table->boolean('open')->default(true);
            $table->string('document_path');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')
                    ->references('id')
                    ->on('patients')
                    ->onDelete('cascade');

            $table->foreign('department_id')
                    ->references('id')
                    ->on('departments')
                    ->onDelete('cascade');

            $table->foreign('fee_type')
                    ->references('id')
                    ->on('payment_types')
                    ->onDelete('cascade');

            $table->foreign('scheme_id')
                    ->references('id')
                    ->on('schemes')
                    ->onDelete('cascade');

            $table->foreign('clinic_id')
                    ->references('id')
                    ->on('clinics')
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
        Schema::dropIfExists('visits');
    }
};
