<?php

use App\Http\Controllers\FiliereController;
use App\Http\Controllers\NiveauController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\EnseignantController;
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

//routes des justificatifs

Route::post("/AccepterUnJustification/{id}",[App\Http\Controllers\JustificatifController::class,"AccepterUnJustification"]);
Route::post("/RefuserUnJustificatif/{id}",[App\Http\Controllers\JustificatifController::class,"RefuserUnJustificatif"]);
Route::post("/ModifierUnJustificatifApresRenvoi/{id}",[App\Http\Controllers\JustificatifController::class,"ModifierUnJustificatifApresRenvoi"]);
Route::post("/renvoyerUnJustificatif/{id}",[App\Http\Controllers\JustificatifController::class,"renvoyerUnJustificatif"]);
Route::post("/CreationDeJustificatif",[App\Http\Controllers\JustificatifController::class,"CreationDeJustificatif"]);
Route::post("/ListerLesJustificatifsParEnseignant/{id}",[App\Http\Controllers\JustificatifController::class,"ListerLesJustificatifsParEnseignant"]);
//fin des justificatifs
//routes pour les presences

Route::post('/ListeDesSessionsManquerParEtudiant/{id}', [App\Http\Controllers\PresenceController::class, 'ListeDesSessionsManquerParEtudiant']);
Route::post("/changerStatut",[App\Http\Controllers\PresenceController::class,"changerStatut"]);
// Route::put("/modifierEtudiant",[App\Http\Controllers\EtudiantController::class,"modifier"]);
Route::post("/modifierEtudiant", [App\Http\Controllers\EtudiantController::class, "modifier"]);
Route::post("/modifierEnseignant", [App\Http\Controllers\EnseignantController::class, "modifier"]);
Route::post("/loginEtudiant",[App\Http\Controllers\EtudiantController::class,"login"]);
Route::post("/registerEtudiant",[App\Http\Controllers\EtudiantController::class,"store"]);
Route::post("/loginEnseignant",[App\Http\Controllers\EnseignantController::class,"login"]);
Route::post("/registerEnseignant",[App\Http\Controllers\EnseignantController::class,"store"]);
Route::post("/loginAdmin",[App\Http\Controllers\AdminController::class,"login"]);

Route::post("/sessionsParEnseignant/{id}",[App\Http\Controllers\EnseignantController::class,"sessionsParEnseignant"]);
Route::post("/sessionsParFiliereEtNiveau",[App\Http\Controllers\SessionController::class,"sessionsParFiliereEtNiveau"]);
Route::post('/getMatieresByEnseignant/{id}', [App\Http\Controllers\MatieresController::class, 'getMatieresByEnseignant']);
Route::post('/getMatieresByEnseignantFiliereAndNiveau/{id}', [App\Http\Controllers\MatieresController::class, 'getMatieresByEnseignantFiliereAndNiveau']);

Route::post('/MatiereImport', [App\Http\Controllers\MatieresController::class, 'ImportExcel']);
Route::post('/SalleImport', [App\Http\Controllers\SalleController::class, 'ImportExcel']);
Route::post('/FiliereImport', [App\Http\Controllers\FiliereController::class, 'ImportExcel']);
Route::post('/EtudiantsImport', [App\Http\Controllers\EtudiantController::class, 'ImportExcel']);
Route::post('/EnseignantsImport', [App\Http\Controllers\EnseignantController::class, 'ImportExcel']);
Route::post('/NiveauImport', [App\Http\Controllers\NiveauController::class, 'ImportExcel']);

// Ajoutés par le dev du FRONT END ------------------------------------------------------------------------------------------

Route::middleware(['auth:etudiant-api,enseignant-api,admin-api'])->group(function () {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'destroy']);
    Route::delete('/notifications', [App\Http\Controllers\NotificationController::class, 'destroyAll']);
});

// // Pour les étudiants
// Route::middleware('auth:etudiant-api')->group(function () {
//     Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
//     Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
//     Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
// });

// // Pour les enseignants
// Route::middleware('auth:enseignant-api')->group(function () {
//     Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
//     Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
//     Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
// });

// // Pour les admins
// Route::middleware('auth:admin-api')->group(function () {
//     Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
//     Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
//     Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
// });


Route::middleware('auth:etudiant-api')->get('/etudiant/me', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:enseignant-api')->get('/enseignant/me', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:admin-api')->get('/admin/me', function (Request $request) {
    return $request->user();
});

Route::post('/presences', [App\Http\Controllers\PresenceController::class, 'ajouterPresence']);
Route::post('/etudiants/{id}/location', [App\Http\Controllers\EtudiantController::class, 'updateLocation']);
Route::get('/sessions/auto-lancer', [App\Http\Controllers\SessionController::class, 'getUpcomingSession']);
Route::get('/enseignants/{id}/sessions-filtrees', [App\Http\Controllers\EnseignantController::class, 'sessionsParEnseignantFiltrees']);
Route::get('/sessions/filter', [App\Http\Controllers\SessionController::class, 'filtrerSessions']);
Route::get('/enseignant', [App\Http\Controllers\EnseignantController::class, 'index']);
Route::post('/enseignants/byfiliereniveau', [App\Http\Controllers\EnseignantController::class, 'getEnseignantsByFiliereAndNiveau']);
Route::post("/matieres/byfiliereniveau", [App\Http\Controllers\MatieresController::class, "getMatieresByFiliereAndNiveau"]); 
Route::post('/sessions/{id}/lancer', [App\Http\Controllers\SessionController::class, 'lancerSession']);
Route::post('/sessions/{id}/terminer', [App\Http\Controllers\SessionController::class, 'terminerSession']);
Route::get('/sessions/{id}/inscrits', [App\Http\Controllers\PresenceController::class, 'getInscritsParSession']);

// Route::post('/etudiants/device-token', [App\Http\Controllers\EtudiantController::class, 'updateDeviceToken']);
// Route::post('/enseignants/device-token', [App\Http\Controllers\EnseignantController::class, 'updateDeviceToken']);
Route::post('/etudiants/{id}/device-token', [App\Http\Controllers\EtudiantController::class, 'updateDeviceToken']);
Route::post('/enseignants/{id}/device-token', [App\Http\Controllers\EnseignantController::class, 'updateDeviceToken']);
// Route::get('/test-fcm', function () {
//     $etudiant = \App\Models\Etudiant::find(1);
//     $controller = new SessionController(); // Crée une instance
//     $controller->envoyerNotificationEtudiant($etudiant, 'Test de notification');
// });
// Route::middleware('auth:enseignant-api')->get('/notifications', function (Request $request) {
//     return $request->user()->notifications;
// });

// afficher les sessions par jour et par semaine
Route::get('/sessionsParSemaineCourante', [App\Http\Controllers\SessionController::class, 'sessionsParSemaineCourante']);
Route::get('/sessionParJourCourant', [App\Http\Controllers\SessionController::class, 'sessionParJourCourant']); 

Route::middleware('auth:etudiant-api')->get('/notifications', function (Request $request) {
    return $request->user()->notifications;
});
// Route::post('/save-fcm-token', [App\Http\Controllers\FirebaseTokenController::class, 'store']);
// Route::post('send-fcm-notification', [App\Http\Controllers\FcmController::class, 'sendFcmNotification']);


// Route::middleware('auth:api')->get('/notifications', function (Request $request) {
//     return $request->user()->notifications;
// });



Route::get("/sessions", [App\Http\Controllers\SessionController::class, "index"]);
Route::get("/matieres", [App\Http\Controllers\MatieresController::class, "index"]);        
Route::post("/addsession", [App\Http\Controllers\SessionController::class, "store"]);       
Route::put("/sessions/{id}", [App\Http\Controllers\SessionController::class, "update"]); 
Route::patch("/sessions/{id}", [App\Http\Controllers\SessionController::class, "update"]);  
Route::delete("/sessions/{id}", [App\Http\Controllers\SessionController::class, "destroy"]);
Route::get("/salles", [App\Http\Controllers\SalleController::class, "index"]);
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
    Route::get("/mesNotifications",[App\Http\Controllers\EtudiantController::class,"mesNotifications"]);
    Route::get("/mesNotificationsNonLues",[App\Http\Controllers\EtudiantController::class,"mesNotificationsNonLues"]);
});
