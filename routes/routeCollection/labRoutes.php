<?php

use App\Http\Controllers\Laboratory\OrderedTestsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'lab'], function(){
    Route::group(['prefix'=>'orderTests'], function(){
        Route::post('create', [OrderedTestsController::class, 'createOrderTest']);
    });

    

});