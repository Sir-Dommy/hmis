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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->string('transaction_reference')->unique(); 
            $table->string('third_party_reference')->nullable(); 
            $table->string('patient_account_no')->nullable(); 
            $table->string('hospital_account_no')->nullable(); 
            $table->string('scheme_name')->nullable(); 
            $table->unsignedBigInteger('scheme_id')->nullable(); 
            $table->dateTime('initiation_time');
            $table->double('amount', 8, 2);
            $table->double('fee', 8, 2)->default(0.00);
            $table->dateTime('receipt_date')->nullable();  
            $table->string('status');
            $table->boolean('is_reversed');    
            $table->dateTime('reverse_date')->nullable();  
            $table->unsignedBigInteger('reversed_by')->nullable();   
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('disabled_by')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('bill_id') // Column name
                  ->references('id') // Target column in the parent table
                  ->on('bills') // Parent table
                  ->onDelete('cascade');

            $table->foreign('reversed_by') // Column name
                    ->references('id') // Target column in the parent table
                    ->on('users') // Parent table
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
        Schema::dropIfExists('transactions');
    }
};
