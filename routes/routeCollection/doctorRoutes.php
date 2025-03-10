<?php

use App\Http\Controllers\Doctor\ConsultationController;
use App\Http\Controllers\Logs\LogsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'doctor'], function(){

    Route::group(['prefix'=>'consultations'], function(){

        Route::post('create', [ConsultationController::class, 'createConsultation']);
    });
});