<?php

use App\Http\Controllers\Admin\BrandsController;
use App\Http\Controllers\Admin\ChronicDiseasesController;
use App\Http\Controllers\Admin\ClinicController;
use App\Http\Controllers\Admin\ConsultationTypesController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DiagnosisController;
use App\Http\Controllers\Admin\DrugFormulationsController;
use App\Http\Controllers\Admin\DrugsController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ImageTestClassesController;
use App\Http\Controllers\Admin\ImageTestRequestsController;
use App\Http\Controllers\Admin\ImageTestTypesController;
use App\Http\Controllers\Admin\LabTestClassesController;
use App\Http\Controllers\Admin\LabTestRequestsController;
use App\Http\Controllers\Admin\LabTestTypesController;
use App\Http\Controllers\Admin\PaymentTypeController;
use App\Http\Controllers\Admin\PhysicalExaminationTypesController;
use App\Http\Controllers\Admin\SchemesController;
use App\Http\Controllers\Admin\ServiceRelated\ServiceController;
use App\Http\Controllers\Admin\ServiceRelated\ServicePriceController;
use App\Http\Controllers\Admin\SymptomsController;
use App\Http\Controllers\BranchController;
use App\Models\Admin\Diagnosis;
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


//chronic diseases 
Route::group(['prefix'=>'chronicDiseases'], function(){

    Route::post('create', [ChronicDiseasesController::class, 'createChronicDisease']);
    Route::put('update', [ChronicDiseasesController::class, 'updateChronicDisease']);
    Route::get('get', [ChronicDiseasesController::class, 'getSingleChronicDisease']);
    Route::get('', [ChronicDiseasesController::class, 'getAllChronicDiseases']);
    Route::put('approve/{id}', [ChronicDiseasesController::class, 'approveChronicDisease']);
    Route::put('softDelete/{id}', [ChronicDiseasesController::class, 'softDeleteChronicDisease']);
    Route::put('restore/{id}', [ChronicDiseasesController::class, 'restoreSoftDeletedChronicDisease']);
    Route::delete('permanentlyDelete/{id}', [ChronicDiseasesController::class, 'permanentlyDelete']);


});

//consultation types 
Route::group(['prefix'=>'consultationTypes'], function(){

    Route::post('create', [ConsultationTypesController::class, 'createConsultationType']);
    Route::put('update', [ConsultationTypesController::class, 'updateConsultationType']);
    Route::get('get', [ConsultationTypesController::class, 'getSingleConsultationType']);
    Route::get('', [ConsultationTypesController::class, 'getAllConsultationTypes']);
    Route::put('approve/{id}', [ConsultationTypesController::class, 'approveConsultationTypes']);
    Route::put('softDelete/{id}', [ConsultationTypesController::class, 'softDeleteConsultationType']);
    Route::put('restore/{id}', [ConsultationTypesController::class, 'restoreSoftDeleted']);
    Route::delete('permanentlyDelete/{id}', [ConsultationTypesController::class, 'permanentlyDelete']);


});

//Diagnosis 
Route::group(['prefix'=>'diagnosis'], function(){

    Route::post('create', [DiagnosisController::class, 'createDiagnosis']);
    Route::put('update', [DiagnosisController::class, 'updateDiagnosis']);
    Route::get('get', [DiagnosisController::class, 'getSingleDiagnosis']);
    Route::get('', [DiagnosisController::class, 'getAllDiagnosis']);
    Route::put('approve/{id}', [DiagnosisController::class, 'approveDiagnosis']);
    Route::put('softDelete/{id}', [DiagnosisController::class, 'softDeleteDiagnosis']);
    Route::put('restore/{id}', [DiagnosisController::class, 'restoreSoftDeleted']);
    Route::delete('permanentlyDelete/{id}', [DiagnosisController::class, 'permanentlyDelete']);


});

//drug formulas
Route::group(['prefix'=>'drugFormulas'], function(){

    Route::post('create', [DrugFormulationsController::class, 'createDrugFormula']);
    Route::put('update', [DrugFormulationsController::class, 'updateDrugFormula']);
    Route::get('get', [DrugFormulationsController::class, 'getSingleDrugFormula']);
    Route::get('', [DrugFormulationsController::class, 'getAllDrugFormulas']);
    Route::put('approve/{id}', [DrugFormulationsController::class, 'approveDrugFormula']);
    Route::put('softDelete/{id}', [DrugFormulationsController::class, 'softDeleteDrugFormula']);
    Route::put('restore/{id}', [DrugFormulationsController::class, 'restoreSoftDeleted']);
    Route::delete('permanentlyDelete/{id}', [DrugFormulationsController::class, 'permanentlyDelete']);


});

//drugs
Route::group(['prefix'=>'drugs'], function(){

    Route::post('create', [DrugsController::class, 'createDrug']);
    Route::put('update', [DrugsController::class, 'updateDrug']);
    Route::get('get', [DrugsController::class, 'getSingleDrug']);
    Route::get('', [DrugsController::class, 'getAllDrugs']);
    Route::put('approve/{id}', [DrugsController::class, 'approveDrug']);
    Route::put('softDelete/{id}', [DrugsController::class, 'softDeleteDrug']);
    Route::put('restore/{id}', [DrugsController::class, 'restoreSoftDeleted']);
    Route::delete('permanentlyDelete/{id}', [DrugsController::class, 'permanentlyDelete']);


});

//physical Examination Types
Route::group(['prefix'=>'physicalExaminationTypes'], function(){

    Route::post('create', [PhysicalExaminationTypesController::class, 'createPhysicalExaminationType']);
    Route::put('update', [PhysicalExaminationTypesController::class, 'updatePhysicalExaminationType']);
    Route::get('get', [PhysicalExaminationTypesController::class, 'getSinglePhysicalExaminationType']);
    Route::get('', [PhysicalExaminationTypesController::class, 'getAllPhysicalExaminationTypes']);
    Route::put('approve/{id}', [PhysicalExaminationTypesController::class, 'approvePhysicalExaminationType']);
    Route::put('softDelete/{id}', [PhysicalExaminationTypesController::class, 'softDeletePhysicalExaminationType']);
    Route::put('restore/{id}', [PhysicalExaminationTypesController::class, 'restoreSoftDeleted']);
    Route::delete('permanentlyDelete/{id}', [PhysicalExaminationTypesController::class, 'permanentlyDelete']);


});

//symptoms
Route::group(['prefix'=>'symptoms'], function(){

    Route::post('create', [SymptomsController::class, 'createSymptom']);
    Route::put('update', [SymptomsController::class, 'updateSymptom']);
    Route::get('get', [SymptomsController::class, 'getSingleSymptom']);
    Route::get('', [SymptomsController::class, 'getAllSymptom']);
    Route::put('approve/{id}', [SymptomsController::class, 'approveSymptom']);
    Route::put('softDelete/{id}', [SymptomsController::class, 'softDeleteSymptom']);
    Route::put('restore/{id}', [SymptomsController::class, 'restoreSoftDeleted']);
    Route::delete('permanentlyDelete/{id}', [SymptomsController::class, 'permanentlyDelete']);


});

//Image Test Classes
Route::group(['prefix'=>'imageTestClasses'], function(){

    Route::post('create', [ImageTestClassesController::class, 'createImageTestClass']);
    Route::put('update', [ImageTestClassesController::class, 'updateImageTestClass']);
    Route::get('get', [ImageTestClassesController::class, 'getSingleImageTestClass']);
    Route::get('', [ImageTestClassesController::class, 'getAllImageTestClass']);
    Route::put('approve/{id}', [ImageTestClassesController::class, 'approveImageTestClass']);
    Route::put('softDelete/{id}', [ImageTestClassesController::class, 'softDeleteImageTestClass']);
    Route::put('restore/{id}', [ImageTestClassesController::class, 'restoreSoftDeleted']);
    Route::delete('permanentlyDelete/{id}', [ImageTestClassesController::class, 'permanentlyDelete']);


});

//Image Test Request
Route::group(['prefix'=>'imageTestRequests'], function(){

    Route::post('create', [ImageTestRequestsController::class, 'createImageTestRequest']);
    Route::put('update', [ImageTestRequestsController::class, 'updateImageTestRequest']);
    Route::get('get', [ImageTestRequestsController::class, 'getSingleImageTestRequest']);
    Route::get('', [ImageTestRequestsController::class, 'getAllImageTestRequest']);
    Route::put('approve/{id}', [ImageTestRequestsController::class, 'approveImageTestRequest']);
    Route::put('softDelete/{id}', [ImageTestRequestsController::class, 'softDeleteImageTestRequest']);
    Route::put('restore/{id}', [ImageTestRequestsController::class, 'restoreSoftDeleted']);
    Route::delete('permanentlyDelete/{id}', [ImageTestRequestsController::class, 'permanentlyDelete']);


});

//Image Test Types
Route::group(['prefix'=>'imageTestTypes'], function(){

    Route::post('create', [ImageTestTypesController::class, 'createImageTestType']);
    Route::put('update', [ImageTestTypesController::class, 'updateImageTestType']);
    Route::get('get', [ImageTestTypesController::class, 'getSingleImageTestType']);
    Route::get('', [ImageTestTypesController::class, 'getAllImageTestType']);
    Route::put('approve/{id}', [ImageTestTypesController::class, 'approveImageTestType']);
    Route::put('softDelete/{id}', [ImageTestTypesController::class, 'softDeleteImageTestType']);
    Route::put('restore/{id}', [ImageTestTypesController::class, 'restoreSoftDeleted']);
    Route::delete('permanentlyDelete/{id}', [ImageTestTypesController::class, 'permanentlyDelete']);


});

//Lab Test Classes
Route::group(['prefix'=>'labTestClasses'], function(){

    Route::post('create', [LabTestClassesController::class, 'createLabTestClass']);
    Route::put('update', [LabTestClassesController::class, 'updateLabTestClass']);
    Route::get('get', [LabTestClassesController::class, 'getSingleLabTestClass']);
    Route::get('', [LabTestClassesController::class, 'getAllLabTestClass']);
    Route::put('approve/{id}', [LabTestClassesController::class, 'approveLabTestClass']);
    Route::put('softDelete/{id}', [LabTestClassesController::class, 'softDeleteLabTestClass']);
    Route::put('restore/{id}', [LabTestClassesController::class, 'restoreSoftDeleted']);
    Route::delete('permanentlyDelete/{id}', [LabTestClassesController::class, 'permanentlyDelete']);


});

//Lab Test Requests
Route::group(['prefix'=>'labTestRequests'], function(){

    Route::post('create', [LabTestRequestsController::class, 'createLabTestRequest']);
    Route::put('update', [LabTestRequestsController::class, 'updateLabTestRequest']);
    Route::get('get', [LabTestRequestsController::class, 'getSingleLabTestRequest']);
    Route::get('', [LabTestRequestsController::class, 'getAllLabTestRequest']);
    Route::put('approve/{id}', [LabTestRequestsController::class, 'approveLabTestRequest']);
    Route::put('softDelete/{id}', [LabTestRequestsController::class, 'softDeleteLabTestRequest']);
    Route::put('restore/{id}', [LabTestRequestsController::class, 'restoreSoftDeleted']);
    Route::delete('permanentlyDelete/{id}', [LabTestRequestsController::class, 'permanentlyDelete']);


});

//Lab Test Types
Route::group(['prefix'=>'labTestTypes'], function(){

    Route::post('create', [LabTestTypesController::class, 'createLabTestType']);
    Route::put('update', [LabTestTypesController::class, 'updateLabTestType']);
    Route::get('get', [LabTestTypesController::class, 'getSingleLabTestType']);
    Route::get('', [LabTestTypesController::class, 'getAllLabTestType']);
    Route::put('approve/{id}', [LabTestTypesController::class, 'approveLabTestType']);
    Route::put('softDelete/{id}', [LabTestTypesController::class, 'softDeleteLabTestType']);
    Route::put('restore/{id}', [LabTestTypesController::class, 'restoreSoftDeleted']);
    Route::delete('permanentlyDelete/{id}', [LabTestTypesController::class, 'permanentlyDelete']);


});


//services
Route::group(['prefix'=>'services'], function(){

    Route::post('create', [ServiceController::class, 'createService']);
    Route::put('update', [ServiceController::class, 'updateService']);
    Route::get('get', [ServiceController::class, 'getSingleService']);
    Route::get('', [ServiceController::class, 'getAllServices']);
    Route::put('approve/{id}', [ServiceController::class, 'approveService']);
    Route::put('disable/{id}', [ServiceController::class, 'disableService']);
    Route::put('restore/{id}', [ServiceController::class, 'restoreSoftDeleteService']);
    Route::put('softDelete/{id}', [ServiceController::class, 'softDeleteService']);
    Route::delete('permanentlyDelete/{id}', [ServiceController::class, 'permanentDeleteService']);


});


//services
Route::group(['prefix'=>'servicePrices'], function(){

    Route::post('create', [ServicePriceController::class, 'createServicePrice']);
    Route::put('update', [ServicePriceController::class, 'updateServicePrice']);
    Route::get('get/{id}', [ServicePriceController::class, 'getSingleServicePrice']);
    Route::get('', [ServicePriceController::class, 'getAllServicePrices']);
    Route::put('approve/{id}', [ServicePriceController::class, 'approveServicePrice']);
    Route::put('disable/{id}', [ServicePriceController::class, 'disableServicePrice']);
    Route::put('restore/{id}', [ServicePriceController::class, 'restoreSoftDeleteServicePrice']);
    Route::put('softDelete/{id}', [ServicePriceController::class, 'softDeleteServicePrice']);
    Route::delete('permanentlyDelete/{id}', [ServicePriceController::class, 'permanentDeleteServicePrice']);


});