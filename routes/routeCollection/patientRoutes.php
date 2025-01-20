<?php

use App\Http\Controllers\Patient\EmergencyVisitController;
use App\Http\Controllers\Patient\PatientController;
use App\Http\Controllers\Patient\VisitController;

use App\Http\Controllers\Patient\VitalController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix'=>'patients'], function(){
    Route::post('create', [PatientController::class, 'createPatient']);
    Route::post('update', [PatientController::class, 'updatePatient']);
    Route::get('get', [PatientController::class, 'getSinglePatient']);
    Route::get('search/{value}', [PatientController::class, 'deepPatientSearch']);
    Route::get('', [PatientController::class, 'getAllPatients']);
    Route::put('approve/{id}', [PatientController::class, 'approvePatient']);
    Route::put('disable/{id}', [PatientController::class, 'disablePatient']);
    Route::put('softDelete/{id}', [PatientController::class, 'softDelete']);
    Route::put('permanentlyDelete/{id}', [PatientController::class, 'permanentlyDelete']);
});


Route::group(['prefix'=>'emergencyVisits'], function(){
    Route::post('create', [EmergencyVisitController::class, 'createEmergencyVisit']);
    Route::put('update', [EmergencyVisitController::class, 'updateEmergencyVisit']);
    Route::get('get/{id}', [EmergencyVisitController::class, 'getSingleEmergencyVisit']);
    Route::get('', [EmergencyVisitController::class, 'getAllEmergencyVisists']);
    Route::put('softDelete/{id}', [EmergencyVisitController::class, 'softDeleteEmergencyVisit']);
    Route::delete('permanentlyDelete/{id}', [EmergencyVisitController::class, 'permanentlyDelete']);
});




Route::group(['prefix'=>'visits'], function(){
    Route::post('create', [VisitController::class, 'createVisit']);
    Route::put('update', [VisitController::class, 'updateVisit']);
    Route::get('get/{id}', [VisitController::class, 'getSingleVisit']);
    Route::get('', [VisitController::class, 'getAllVisits']);
    Route::put('softDelete/{id}', [VisitController::class, 'softDeleteVisit']);
    Route::delete('permanentlyDelete/{id}', [VisitController::class, 'permanentlyDelete']);
});


Route::group(['prefix'=>'vitals'], function(){
    Route::post('create', [VitalController::class, 'createVital']);
    Route::put('update', [VitalController::class, 'updateVital']);
    Route::get('get', [VitalController::class, 'getSingleVital']);
    Route::get('', [VitalController::class, 'getAllVitals']);
    Route::put('softDelete/{id}', [VitalController::class, 'softDeleteVital']);
    Route::delete('permanentlyDelete/{id}', [VitalController::class, 'permanentlyDeleteVital']);
});