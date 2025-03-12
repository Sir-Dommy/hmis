<?php

use App\Http\Controllers\Pharmacy\PrescriptionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'pharmacy'], function(){
    Route::group(['prefix'=>'prescriptions'], function(){
        Route::post('create', [PrescriptionController::class, 'createPrescription']);
        Route::post('get', [PrescriptionController::class, 'getSinglePrescription']);
        Route::post('getWithVisitId', [PrescriptionController::class, 'getPrescriptionByVisitId']);
        Route::post('/', [PrescriptionController::class, 'getAllPrescriptions']);
        Route::post('softDelete', [PrescriptionController::class, 'softDeletePrescription']);
        Route::post('permanentlyDelete', [PrescriptionController::class, 'permanentlyDeletePrescription']);


        //test route
        Route::post('test', [PrescriptionController::class, 'test']);
    });

    

});