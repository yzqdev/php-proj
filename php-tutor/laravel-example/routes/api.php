<?php

use App\Http\Controllers\CatController;
use App\Http\Controllers\HomeController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix("home")->group(function (){
    Route::get("/getHome",[HomeController::class,'getHomeData']);
    Route::get("/hhh",[HomeController::class,'removeAllHome']);
});

Route::prefix("cat")->group(function (){
    Route::get("/createOne",[CatController::class,'create']);
    Route::get("/show",[CatController::class,'show']);
});
