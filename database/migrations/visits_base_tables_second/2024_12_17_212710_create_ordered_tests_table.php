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
        Schema::create('ordered_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visit_id');
            $table->unsignedBigInteger('lab_test_type_id')->nullable();
            $table->unsignedBigInteger('image_test_type_id')->nullable();
            $table->unsignedBigInteger('lab_test_class_id')->nullable();
            $table->unsignedBigInteger('image_test_class_id')->nullable();
            $table->string('clinical_information')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('end_time')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('image_test_class_id') // Column name
                  ->references('id') // Target column in the parent table
                  ->on('image_test_classes') // Parent table
                  ->onDelete('cascade');

            $table->foreign('image_test_type_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('image_test_types') // Parent table
                ->onDelete('cascade');

            $table->foreign('lab_test_class_id') // Column name
                  ->references('id') // Target column in the parent table
                  ->on('lab_test_classes') // Parent table
                  ->onDelete('cascade');

            $table->foreign('lab_test_type_id') // Column name
                  ->references('id') // Target column in the parent table
                  ->on('lab_test_types') // Parent table
                  ->onDelete('cascade');

            $table->foreign('visit_id') // Column name
                  ->references('id') // Target column in the parent table
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
        Schema::dropIfExists('ordered_tests');
    }
};
