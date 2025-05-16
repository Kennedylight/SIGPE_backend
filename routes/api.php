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
Route::post("/sessionsParEnseignant/{id}",[App\Http\Controllers\EnseignantController::class,"sessionsParEnseignant"]);
Route::post("/sessionsParFiliereEtNiveau",[App\Http\Controllers\sessionController::class,"sessionsParFiliereEtNiveau"]);
Route::post('/getMatieresByEnseignant/{id}', [App\Http\Controllers\MatieresController::class, 'getMatieresByEnseignant']);
Route::post('/getMatieresByEnseignantFiliereAndNiveau/{id}', [App\Http\Controllers\MatieresController::class, 'getMatieresByEnseignantFiliereAndNiveau']);

Route::post('/MatiereImport', [App\Http\Controllers\MatieresController::class, 'ImportExcel']);
Route::post('/SalleImport', [App\Http\Controllers\SalleController::class, 'ImportExcel']);
Route::post('/FiliereImport', [App\Http\Controllers\FiliereController::class, 'ImportExcel']);
Route::post('/EtudiantsImport', [App\Http\Controllers\EtudiantController::class, 'ImportExcel']);
Route::post('/EnseignantsImport', [App\Http\Controllers\EnseignantController::class, 'ImportExcel']);
Route::post('/NiveauImport', [App\Http\Controllers\NiveauController::class, 'ImportExcel']);


Route::get("/sessions", [App\Http\Controllers\sessionController::class, "index"]);        
Route::post("/addsession", [App\Http\Controllers\sessionController::class, "store"]);       
Route::put("/sessions/{id}", [App\Http\Controllers\sessionController::class, "update"]);   
Route::delete("/sessions/{id}", [App\Http\Controllers\sessionController::class, "destroy"]);
Route::get("/niveaux", [App\Http\Controllers\NiveauController::class, "index"]);
Route::post("/addniveau", [App\Http\Controllers\NiveauController::class, "store"]);
Route::put("/niveaux/{id}", [App\Http\Controllers\NiveauController::class, "update"]);
Route::delete("/niveaux/{id}", [App\Http\Controllers\NiveauController::class, "destroy"]);
Route::get("/filiere", [App\Http\Controllers\FiliereController::class, "index"]);
Route::post("/addfiliere", [App\Http\Controllers\FiliereController::class, "store"]);
Route::put("/filiere/{id}", [App\Http\Controllers\FiliereController::class, "update"]);
Route::post("/importerExcel", [App\Http\Controllers\TestController::class, "importerExcel"]);
Route::delete("/filiere/{id}", [App\Http\Controllers\FiliereController::class, "destroy"]);


Route::middleware('auth:api')->group(function () {
    
});
