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
        Schema::create('transaction_change_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->string('initial_patient_account_no')->nullable(); 
            $table->string('update_patient_account_no')->nullable(); 
            $table->string('initial_hospital_account_no')->nullable(); 
            $table->string('update_hospital_account_no')->nullable(); 
            $table->string('initial_scheme_name')->nullable(); 
            $table->string('update_scheme_name')->nullable(); 
            $table->unsignedBigInteger('initial_scheme_id')->nullable(); 
            $table->unsignedBigInteger('update_scheme_id')->nullable(); 
            $table->double('initial_amount', 8, 2); 
            $table->double('update_amount', 8, 2);
            $table->double('initial_fee', 8, 2);
            $table->double('update_fee', 8, 2);  
            $table->string('initial_status');  
            $table->string('update_status');    
            $table->string('initial_reason')->nullable();
            $table->string('update_reason')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('disabled_by')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('transaction_id') // Column name
                  ->references('id') // Target column in the parent table
                  ->on('transactions') // Parent table
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

            $table->foreign('approved_by') // Column name
                ->references('id') // Target column in the parent table
                ->on('users') // Parent table
                ->onDelete('cascade');

            $table->foreign('disabled_by') // Column name
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
        Schema::dropIfExists('transaction_change_requests');
    }
};
