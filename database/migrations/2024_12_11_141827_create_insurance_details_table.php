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
        Schema::create('insurance_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('insurer_id')->nullable();
            $table->unsignedBigInteger('scheme_type_id')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('insurance_card_path')->nullable();
            $table->string('principal_member_name')->nullable();
            $table->string('principal_member_number')->nullable();
            $table->string('member_validity');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('insurer_id')
                    ->references('id')
                    ->on('schemes')
                    ->onDelete('cascade');

            $table->foreign('scheme_type_id')
                    ->references('id')
                    ->on('scheme_types')
                    ->onDelete('cascade');

            $table->foreign('patient_id')
                    ->references('id')
                    ->on('patients')
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
        Schema::dropIfExists('insurance_details');
    }
};
