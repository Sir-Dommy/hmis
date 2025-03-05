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
        Schema::create('item_movements', function (Blueprint $table) {
            $table->id();
            $table->enum('movement_type', ['Purchase', 'Sale', 'Adjustment']);
            $table->unsignedBigInteger('service_item_id');
            $table->double('cost_price', 10, 2);
            $table->double('sell_price', 10, 2)->nullable();
            $table->double('total_amount', 10, 2);
            $table->double('quantity', 10, 2);
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('sub_account_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('disabled_by')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('service_item_id') // Column name
                    ->references('id') // Target column in the parent table
                    ->on('service_prices') // Parent table
                    ->onDelete('cascade');

            $table->foreign('unit_id') // Column name
                    ->references('id') // Target column in the parent table
                    ->on('units') // Parent table
                    ->onDelete('cascade');

            $table->foreign('sub_account_id') // Column name
                    ->references('id') // Target column in the parent table
                    ->on('sub_accounts') // Parent table
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
        Schema::dropIfExists('item_movements');
    }
};
