<?php

use App\Http\Controllers\Bill\BillController;
use App\Http\Controllers\Bill\TransactionController;
use App\Http\Controllers\Nurse\NurseReportController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'nurse'], function(){
    Route::group(['prefix'=>'nurseReports'], function(){

        Route::post('create', [NurseReportController::class, 'createNurseReport']);
        Route::put('update', [NurseReportController::class, 'updateNurseReport']);
        Route::get('get', [NurseReportController::class, 'getNurseReports']);
        Route::get('', [NurseReportController::class, 'getAllNurseReports']);
        Route::put('softDelete/{id}', [NurseReportController::class, 'softDelete']);
        Route::put('restore/{id}', [NurseReportController::class, 'restoreSoftDeleted']);
        Route::delete('permanentlyDelete/{id}', [NurseReportController::class, 'permanentlyDelete']);
    
        
    });
    
});
