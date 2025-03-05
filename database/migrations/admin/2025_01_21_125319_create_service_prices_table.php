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
        Schema::create('service_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('consultation_category_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->unsignedBigInteger('payment_type_id')->nullable();
            $table->unsignedBigInteger('scheme_id')->nullable();
            $table->unsignedBigInteger('scheme_type_id')->nullable();
            $table->unsignedBigInteger('consultation_type_id')->nullable();
            $table->unsignedBigInteger('visit_type_id')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->time('price_applies_from')->nullable();
            $table->time('price_applies_to')->nullable();
            $table->double('duration', 8, 4)->nullable();
            $table->unsignedBigInteger('lab_test_type_id')->nullable();
            $table->unsignedBigInteger('image_test_type_id')->nullable();
            $table->unsignedBigInteger('drug_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('wing_id')->nullable();
            $table->unsignedBigInteger('ward_id')->nullable();
            $table->unsignedBigInteger('office_id')->nullable();
            $table->enum('category', ['Drug', 'Non-Drug', 'Others']);
            $table->unsignedBigInteger('unit_id');
            $table->double('smallest_sellable_quantity', 8, 4);
            $table->double('cost_price', 8, 4);
            $table->double('selling_price')->default(0.0);
            $table->enum('mark_up_type', ['Percentage', 'Fixed'])->nullable();
            $table->double('mark_up_value')->default(0.0);
            $table->enum('promotion_type', ['Percentage', 'Fixed'])->nullable();
            $table->double('promotion_value')->default(0.0);
            $table->unsignedBigInteger('income_account_id');
            $table->unsignedBigInteger('asset_account_id');
            $table->unsignedBigInteger('expense_account_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('disabled_by')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('service_id') // Column name
                  ->references('id') // Target column in the parent table
                  ->on('services') // Parent table
                  ->onDelete('cascade');

            $table->foreign('department_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('departments') // Parent table
                ->onDelete('cascade');

            $table->foreign('consultation_category_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('consultation_categories') // Parent table
                ->onDelete('cascade');

            $table->foreign('clinic_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('clinics') // Parent table
                ->onDelete('cascade');

            $table->foreign('payment_type_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('payment_types') // Parent table
                ->onDelete('cascade');

            $table->foreign('scheme_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('schemes') // Parent table
                ->onDelete('cascade');

            $table->foreign('scheme_type_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('scheme_types') // Parent table
                ->onDelete('cascade');

            $table->foreign('consultation_type_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('consultation_types') // Parent table
                ->onDelete('cascade');

            $table->foreign('visit_type_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('visit_types') // Parent table
                ->onDelete('cascade');

            $table->foreign('doctor_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('employees') // Parent table
                ->onDelete('cascade');

            $table->foreign('lab_test_type_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('lab_test_types') // Parent table
                ->onDelete('cascade');

            $table->foreign('image_test_type_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('image_test_types') // Parent table
                ->onDelete('cascade');

            $table->foreign('drug_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('drugs') // Parent table
                ->onDelete('cascade');

            $table->foreign('brand_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('brands') // Parent table
                ->onDelete('cascade');

            $table->foreign('branch_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('branches') // Parent table
                ->onDelete('cascade');

            $table->foreign('building_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('buildings') // Parent table
                ->onDelete('cascade');

            $table->foreign('wing_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('wings') // Parent table
                ->onDelete('cascade');

            $table->foreign('ward_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('wards') // Parent table
                ->onDelete('cascade');

            $table->foreign('office_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('offices') // Parent table
                ->onDelete('cascade');

            $table->foreign('unit_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('units') // Parent table
                ->onDelete('cascade');

            $table->foreign('income_account_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('sub_accounts') // Parent table
                ->onDelete('cascade');

            $table->foreign('asset_account_id') // Column name
                ->references('id') // Target column in the parent table
                ->on('sub_accounts') // Parent table
                ->onDelete('cascade');

            $table->foreign('expense_account_id') // Column name
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
        Schema::dropIfExists('service_prices');
    }
};
