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
        Schema::create('vitals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visits_id');
            $table->decimal('weight', 5, 2)->nullable();
            $table->string('blood_pressure')->nullable();
            $table->string('blood_glucose')->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->string('blood_type')->nullable();
            $table->string('disease')->nullable();
            $table->string('allergies')->nullable();
            $table->text('nursing_remarks')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('visits_id')
            ->references('id')
            ->on('visits')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vitals');
    }
};