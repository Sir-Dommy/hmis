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
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('service_item_id')->nullable();
            $table->unsignedBigInteger('item_movement_id')->nullable();
            $table->unsignedBigInteger('unit_id');
            $table->double('selling_price', 10, 2);
            $table->double('total_amount', 10, 2);
            $table->double('discount', 10, 2)->default(0.00);
            $table->string('description')->nullable();
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

            $table->foreign('service_item_id') // Column name
                    ->references('id') // Target column in the parent table
                    ->on('service_prices') // Parent table
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
        Schema::dropIfExists('bill_items');
    }
};
