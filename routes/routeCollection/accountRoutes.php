<?php

use App\Http\Controllers\Accounts\MainAccountsController;
use App\Http\Controllers\Accounts\SubAccountsController;
use App\Http\Controllers\Accounts\UnitsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'mainAccounts'], function(){

    Route::get('', [MainAccountsController::class, 'getAllMainAccounts']);
    Route::get('get', [MainAccountsController::class, 'getSingleMainAccount']);
    Route::post('create', [MainAccountsController::class, 'createMainAccount']);
    Route::put('update', [MainAccountsController::class, 'updateMainAccounts']);
    Route::put('approve/{id}', [MainAccountsController::class, 'approveMainAccount']);
    Route::put('disable/{id}', [MainAccountsController::class, 'disableMainAccount']);
    Route::put('softDelete/{id}', [MainAccountsController::class, 'softDeleteMainAccount']);
    Route::put('restore/{id}', [MainAccountsController::class, 'restoreSoftDeleteMainAccount']);
    Route::delete('permanentlyDelete/{id}', [MainAccountsController::class, 'permanentDeleteMainAccount']);
});


Route::group(['prefix'=>'subAccounts'], function(){

    Route::get('', [SubAccountsController::class, 'getAllSubAccounts']);
    Route::get('get', [SubAccountsController::class, 'getSingleSubAccount']);
    Route::post('create', [SubAccountsController::class, 'createSubAccount']);
    Route::put('update', [SubAccountsController::class, 'updateSubAccounts']);
    Route::put('approve/{id}', [SubAccountsController::class, 'approveSubAccount']);
    Route::put('disable/{id}', [SubAccountsController::class, 'disableSubAccount']);
    Route::put('softDelete/{id}', [SubAccountsController::class, 'softDeleteSubAccount']);
    Route::put('restore/{id}', [SubAccountsController::class, 'restoreSoftDeleteSubAccount']);
    Route::delete('permanentlyDelete/{id}', [SubAccountsController::class, 'permanentDeleteSubAccount']);
});


Route::group(['prefix'=>'units'], function(){

    Route::get('', [UnitsController::class, 'getAllUnits']);
    Route::get('get', [UnitsController::class, 'getSingleUnit']);
    Route::post('create', [UnitsController::class, 'createUnit']);
    Route::put('update', [UnitsController::class, 'updateUnit']);
    Route::put('approve/{id}', [UnitsController::class, 'approveUnit']);
    Route::put('disable/{id}', [UnitsController::class, 'disableUnits']);
    Route::put('softDelete/{id}', [UnitsController::class, 'softDeleteUnit']);
    Route::put('restore/{id}', [UnitsController::class, 'restoreSoftDeleteUnit']);
    Route::delete('permanentlyDelete/{id}', [UnitsController::class, 'permanentDeleteUnit']);
});