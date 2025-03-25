<?php

use App\Http\Controllers\Bill\TransactionController;
use App\Http\Controllers\Test\CallBactTestController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'callback'], function(){
    Route::match(['get', 'post'], 'collect', [CallBactTestController::class, 'collect']);
    Route::match(['get', 'post'], 'disburse', [CallBactTestController::class, 'disburse']);

    // Route::get('collect', [CallBactTestController::class, 'collect']);
    // Route::get('disburse', [CallBactTestController::class, 'disburse']);
    
});