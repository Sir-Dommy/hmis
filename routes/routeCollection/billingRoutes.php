<?php

use App\Http\Controllers\Bill\TransactionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'payments'], function(){

    Route::post('cash', [TransactionController::class, 'receiveCashPayment']);
});