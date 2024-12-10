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


Route::group(['prefix'=>'emeregencyVisits'], function(){
    Route::post('create', [PatientController::class, 'createEmergencyVisit']);
    Route::put('update', [PatientController::class, 'updateEmergencyVisit']);
    Route::get('get/{id}', [PatientController::class, 'getSinglePatient']);
    Route::get('', [EmergencyVisitController::class, 'getAllEmergencyVisists']);
    Route::put('softDelete/{id}', [PatientController::class, 'softDeleteEmergencyVisit']);
    Route::delete('permanentlyDelete/{id}', [PatientController::class, 'permanentlyDelete']);
});