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
        Schema::create('error_log', function (Blueprint $table) {
            $table->id();
            $table->string('class');
            $table->string('method_and_line_number');
            $table->string('error_description');
            $table->unsignedBigInteger('related_user')->nullable();
            $table->string('related_user_ip');
            $table->timestamps();

            $table->foreign('related_user') // Column name
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
        Schema::dropIfExists('error_log');
    }

    
};
