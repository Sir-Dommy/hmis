<?php

use App\Http\Controllers\Doctor\ConsultationController;
use App\Http\Controllers\Laboratory\OrderedTestsController;
use App\Http\Controllers\Logs\LogsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'lab'], function(){
    Route::group(['prefix'=>'orderTests'], function(){
        Route::post('create', [OrderedTestsController::class, 'createOrderTest']);
    });

    

});