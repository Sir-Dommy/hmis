<?php

use App\Http\Controllers\Admin\BrandsController;
use App\Http\Controllers\Admin\ChronicDiseasesController;
use App\Http\Controllers\Admin\ClinicController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\PaymentTypeController;
use App\Http\Controllers\Admin\SchemesController;
use App\Http\Controllers\BranchController;
use Illuminate\Support\Facades\Route;

//department routes
Route::group(['prefix'=>'departments'], function(){

    Route::post('create', [DepartmentController::class, 'createDepartment']);
    Route::put('update', [DepartmentController::class, 'updateDepartment']);
    Route::get('get', [DepartmentController::class, 'getSingleDepartment']);
    Route::get('', [DepartmentController::class, 'getAllDepartments']);
    Route::put('approve/{id}', [DepartmentController::class, 'approveDepartment']);
    Route::put('disable/{id}', [DepartmentController::class, 'disableDepartment']);
});

//employee routes
Route::group(['prefix'=>'employees'], function(){
    Route::post('create', [EmployeeController::class, 'createEmployee']);
    Route::put('update', [EmployeeController::class, 'updateEmployee']);
    Route::get('get', [EmployeeController::class, 'getSingleEmployee']);
    Route::get('', [EmployeeController::class, 'getAllEmployees']);
    Route::put('approve/{id}', [EmployeeController::class, 'approveEmployee']);
    Route::put('disable/{id}', [EmployeeController::class, 'disableEmployee']);

});


//schemes routes 
Route::group(['prefix'=>'schemes'], function(){

    Route::post('create', [SchemesController::class, 'createScheme']);
    Route::put('update', [SchemesController::class, 'updateScheme']);
    Route::get('get', [SchemesController::class, 'getSingleScheme']);
    Route::get('', [SchemesController::class, 'getAllSchemes']);
    Route::put('approve/{id}', [SchemesController::class, 'approveScheme']);
    Route::put('disable/{id}', [SchemesController::class, 'disableScheme']);
    Route::put('softDelete/{id}', [SchemesController::class, 'softDeleteScheme']);
    Route::delete('permanentlyDelete/{id}', [SchemesController::class, 'permanentDeleteScheme']);

});


//schemes clinics 
Route::group(['prefix'=>'clinics'], function(){

    Route::post('create', [ClinicController::class, 'createClinic']);
    Route::put('update', [ClinicController::class, 'updateClinic']);
    Route::get('get', [ClinicController::class, 'getSingleClinic']);
    Route::get('', [ClinicController::class, 'getAllClinics']);
    Route::put('approve/{id}', [ClinicController::class, 'approveClinic']);
    Route::put('softDelete/{id}', [ClinicController::class, 'softDelete']);
    Route::delete('permanentlyDelete/{id}', [ClinicController::class, 'permanentlyDelete']);

});


//schemes payment types 
Route::group(['prefix'=>'paymentTypes'], function(){

    Route::post('create', [PaymentTypeController::class, 'createPaymentType']);
    Route::put('update', [PaymentTypeController::class, 'updatePaymentType']);
    Route::get('get', [PaymentTypeController::class, 'getSinglePaymentType']);
    Route::get('', [PaymentTypeController::class, 'getAllPaymentTypes']);
    Route::put('approve/{id}', [PaymentTypeController::class, 'approvePaymentType']);
    Route::put('softDelete/{id}', [PaymentTypeController::class, 'softDelete']);
    Route::delete('permanentlyDelete/{id}', [PaymentTypeController::class, 'permanentlyDelete']);

});

//schemes payment types 
Route::group(['prefix'=>'brands'], function(){

    Route::post('create', [BrandsController::class, 'createBrand']);
    Route::put('update', [BrandsController::class, 'updateBrand']);
    Route::get('get', [BrandsController::class, 'getSingleBrand']);
    Route::get('', [BrandsController::class, 'getAllBrands']);
    Route::put('approve/{id}', [BrandsController::class, 'approveBrand']);
    Route::put('softDelete/{id}', [BrandsController::class, 'softDeleteBrand']);
    Route::put('restore/{id}', [BrandsController::class, 'restoreSoftDeletedBrand']);
    Route::delete('permanentlyDelete/{id}', [BrandsController::class, 'permanentlyDelete']);


});


//schemes payment types 
Route::group(['prefix'=>'chronicDiseases'], function(){

    Route::post('create', [ChronicDiseasesController::class, 'createChronicDisease']);
    Route::put('update', [BrandsController::class, 'updateChronicDisease']);
    Route::get('get', [BrandsController::class, 'getSingleChronicDisease']);
    Route::get('', [BrandsController::class, 'getAllChronicDiseases']);
    Route::put('approve/{id}', [BrandsController::class, 'approveChronicDisease']);
    Route::put('softDelete/{id}', [BrandsController::class, 'softDeleteChronicDisease']);
    Route::put('restore/{id}', [BrandsController::class, 'restoreSoftDeletedChronicDisease']);
    Route::delete('permanentlyDelete/{id}', [BrandsController::class, 'permanentlyDelete']);


});