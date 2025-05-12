<?php

use App\Http\Controllers\FiliereController;
use App\Http\Controllers\NiveauController;
use App\Http\Controllers\sessionController;
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
Route::post("/sessionsParEnseignant",[App\Http\Controllers\EnseignantController::class,"sessionsParEnseignant"]);
Route::post("/sessionsParFiliereEtNiveau",[App\Http\Controllers\sessionController::class,"sessionsParFiliereEtNiveau"]);
Route::post('/getMatieresByEnseignant', [App\Http\Controllers\MatieresController::class, 'getMatieresByEnseignant']);
Route::post('/getMatieresByEnseignantFiliereAndNiveau', [App\Http\Controllers\MatieresController::class, 'getMatieresByEnseignantFiliereAndNiveau']);
Route::apiResource('sessions', sessionController::class);
Route::apiResource('niveaux', NiveauController::class);
Route::apiResource('sessions', NiveauController::class);
Route::apiResource('filiere', FiliereController::class);

Route::middleware('auth:api')->group(function () {
    
});
