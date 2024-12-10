<?php

use App\Http\Controllers\Patient\EmergencyVisitController;
use App\Http\Controllers\Patient\PatientController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix'=>'patients'], function(){
    Route::post('create', [PatientController::class, 'createPatient']);
    Route::put('update', [PatientController::class, 'updatePatient']);
    Route::get('get', [PatientController::class, 'getSinglePatient']);
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