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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visit_id');
            $table->unsignedBigInteger('drug_id');
            $table->unsignedBigInteger('drug_formula_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('dosage_instruction')->nullable();
            $table->string('prescription_instruction')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('end_time')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('brand_id') // Column name
                  ->references('id') 
                  ->on('brands') // Parent table
                  ->onDelete('cascade');

            $table->foreign('drug_formula_id') // Column name
                  ->references('id') 
                  ->on('drug_formulations') // Parent table
                  ->onDelete('cascade');

            $table->foreign('drug_id') // Column name
                  ->references('id') 
                  ->on('drugs') // Parent table
                  ->onDelete('cascade');

            $table->foreign('visit_id') // Column name
                  ->references('id') 
                  ->on('visits') // Parent table
                  ->onDelete('cascade');

            $table->foreign('created_by') // Column name
                  ->references('id') // Target column in the parent table
                  ->is_null(false)
                  ->on('users') // Parent table
                  ->onDelete('cascade');

            $table->foreign('updated_by') // Column name
                ->references('id') // Target column in the parent table
                ->on('users') // Parent table
                ->onDelete('cascade');

            $table->foreign('deleted_by') // Column name
                ->references('id') // Target column in the parent table
                ->on('users') // Parent table
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
