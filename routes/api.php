<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post("/loginEtudiant",[App\Http\Controllers\EtudiantController::class,"login"]);
Route::post("/registerEtudiant",[App\Http\Controllers\EtudiantController::class,"store"]);
Route::post("/loginEnseignant",[App\Http\Controllers\EnseignantController::class,"login"]);
Route::post("/registerEnseignant",[App\Http\Controllers\EnseignantController::class,"store"]);
Route::middleware('auth:api')->group(function () {
    
});
