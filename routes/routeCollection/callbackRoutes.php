<?php

use App\Http\Controllers\Bill\TransactionController;
use App\Http\Controllers\Test\CallBactTestController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'callback'], function(){
    Route::post('collect', [CallBactTestController::class, 'collect']);
    
});