<?php

use App\Http\Controllers\Doctor\AdmissionsController;
use App\Http\Controllers\Doctor\ConsultationController;
use App\Http\Controllers\Logs\LogsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'doctor'], function(){

    Route::group(['prefix'=>'consultations'], function(){

        Route::post('create', [ConsultationController::class, 'createConsultation']);
        Route::put('update', [ConsultationController::class, 'updateConsultation']);
        Route::get('get', [ConsultationController::class, 'getConsultation']);
        Route::get('/', [ConsultationController::class, 'getAllConsultations']);
        Route::put('softDelete/{id}', [ConsultationController::class, 'softDeleteConsultation']);
        Route::put('restore/{id}', [ConsultationController::class, 'restoreConsultation']);
        Route::delete('permanentlyDelete/{id}', [ConsultationController::class, 'permanentlyDelete']);

    });
});

Route::group(['prefix'=>'admissions'], function(){
    Route::post('create', [AdmissionsController::class, 'createAdmission']);
    Route::put('update', [AdmissionsController::class, 'updateAdmission']);
    Route::get('get/{id}', [AdmissionsController::class, 'getSingleAdmission']);
    Route::get('/', [AdmissionsController::class, 'getAdmissions']);
    // Route::put('softDelete/{id}', [ConsultationController::class, 'softDeleteConsultation']);
    // Route::put('restore/{id}', [ConsultationController::class, 'restoreConsultation']);
    // Route::delete('permanentlyDelete/{id}', [ConsultationController::class, 'permanentlyDelete']);

    
});