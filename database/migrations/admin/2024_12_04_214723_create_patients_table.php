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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code')->unique();
            $table->string('firstname');
            $table->string('lastname');
            $table->date('dob');
            $table->string('identification_type')->nullable();
            $table->string('id_no')->nullable()->unique();
            $table->string('scan_id_photo')->nullable();
            $table->string('phonenumber1')->nullable()->unique();
            $table->string('phonenumber2')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('insurance_membership')->nullable();
            $table->string('address');
            $table->string('residence');
            $table->string('next_of_kin_name');
            $table->string('next_of_kin_contact');
            $table->string('next_of_kin_relationship');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('disabled_by')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

            $table->foreign('approved_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

            $table->foreign('updated_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

            $table->foreign('disabled_by')
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
        Schema::dropIfExists('patients');
    }
};
