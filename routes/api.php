<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Bill\BillController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\Patient\EmergencyVisitController;
use App\Http\Controllers\PaymentPathsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('test', [AuthController::class, 'test']);
Route::post('login', [AuthController::class, 'login']);

// open routes for payments
Route::group(['prefix'=>'payment'], function(){
    Route::post('mpesa', [BillController::class, 'testMpesaPayment']);
    // Route::post('paybill', [AuthController::class, 'testPaybillPayment']);
    // Route::post('pay', [AuthController::class, 'testPayment']);
    // Route::post('g_pay', [AuthController::class, 'testGPayPayment']);
});


//test callback routes for g_pay
require_once __DIR__.'/routeCollection/callbackRoutes.php';


// authenticated routes to require jwt validation
Route::middleware('jwt.auth')->group(function(){

    //admin routes only
    Route::group(['middleware' => ['roles.check:admin']], function(){
        Route::get('branches', [BranchController::class, 'index']);
        Route::post('branches', [BranchController::class, 'store']);
        Route::put('branches', [BranchController::class, 'update']);
        Route::delete('branches', [BranchController::class, 'destroy']);
        Route::get('getBranchesAndRoles', [BranchController::class, 'getBranchesAndRoles']);
        Route::post('register', [AuthController::class, 'register']);


        //payment paths route to view only.... payment paths will not be edited from the frontend to avoid unexpected errors
        Route::group(['prefix'=>'paymentPaths'], function(){

            Route::get('get', [PaymentPathsController::class, 'getSinglePaymentPath']);
            Route::get('', [PaymentPathsController::class, 'getAllPaymentPaths']);
        
        
        });


        require_once __DIR__.'/routeCollection/logRoutes.php';
        require_once __DIR__.'/routeCollection/adminRoutes.php';
        require_once __DIR__.'/routeCollection/doctorRoutes.php';
        require_once __DIR__.'/routeCollection/labRoutes.php';
        require_once __DIR__.'/routeCollection/pharmacyRoutes.php';
        require_once __DIR__.'/routeCollection/billingRoutes.php';
        require_once __DIR__.'/routeCollection/accountRoutes.php';

    });


    require_once __DIR__.'/routeCollection/patientRoutes.php';


    Route::post('logout', [AuthController::class, 'logout']);
    
});
