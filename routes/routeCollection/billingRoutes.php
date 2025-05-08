<?php

use App\Http\Controllers\Bill\BillController;
use App\Http\Controllers\Bill\TransactionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'payments'], function(){

    Route::group(['prefix'=>'cash'], function(){
        Route::post('clear', [TransactionController::class, 'clearBillUsingCashPayment']);
        Route::post('paySpecificItems', [TransactionController::class, 'payForSpecificBillItemsUsingCash']);
    });
    
});

Route::group(['prefix'=>'bills'], function(){
    Route::post('get', [BillController::class, 'selectBills']); 
    
});