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
        Schema::create('consultation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('disabled_by')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('consultation_types');
    }
};
