<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',[AuthController::class, 'login']);

Route::group(['middleware' => 'auth:api'], function(){

    //User crud routes
    Route::get('/users', [UserController::class, 'getAllUsers']);
    Route::get('/users/{id}',[UserController::class, 'getSingleUserByID']);
    Route::post('/users', [UserController::class, 'createUser']);
    Route::put('/users/{id}',[UserController::class, 'updateUserByID']);
    Route::delete('/users/{id}',[UserController::class, 'deleteUserByID']);


    //import csv file to bulk create, update, delete

    Route::post('/users/csv/bulk/{action}', [UserController::class, 'handleBulkAction']);
});
