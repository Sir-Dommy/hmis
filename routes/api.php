<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\Patient\EmergencyVisitController;
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


// authenticated routes to require jwt validation
Route::middleware('jwt.auth')->group(function(){

    //admin routes only
    Route::group(['middleware' => ['roles.check:hod,admin']], function(){
        Route::get('branches', [BranchController::class, 'index']);
        Route::post('branches', [BranchController::class, 'store']);
        Route::put('branches', [BranchController::class, 'update']);
        Route::delete('branches', [BranchController::class, 'destroy']);
        Route::get('getBranchesAndRoles', [BranchController::class, 'getBranchesAndRoles']);
        Route::post('register', [AuthController::class, 'register']);


        require_once __DIR__.'/routeCollection/logRoutes.php';
        require_once __DIR__.'/routeCollection/adminRoutes.php';
        require_once __DIR__.'/routeCollection/patientRoutes.php';

    });


    Route::post('logout', [AuthController::class, 'logout']);
    
});
