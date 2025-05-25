<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\Etudiant;
use App\Models\Presence;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Notifiable;
use App\Notifications\SessionLancee;
use Illuminate\Support\Facades\Notification;

use App\Services\FirebaseNotificationService;


class sessionController extends Controller
{
    // Ajoué per le dev du FRONT END --------------------------------------------------------------------
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    
public function sessionsParFiliereEtNiveau(Request $request)
{
    $request->validate([
        'filiere_id' => 'required|integer|exists:filieres,id',
        'niveau_id' => 'required|integer|exists:niveaux,id'
    ]);

    $sessions = Session::where('filiere_id', $request->filiere_id)
        ->where('niveau_id', $request->niveau_id)
        ->with(['matiere', 'salle', 'enseignant', 'niveau', 'filiere'])
        ->get();

    return response()->json($sessions);
}

public function index()
{
    return response()->json(Session::all());
}

public function store(Request $request)
{
    $validated = $request->validate([
        'statut' => 'required|string',
        'heure_debut' => 'required|date',
        'heure_fin' => 'required|date|after:heure_debut',
        'lien' => 'nullable|string',
        'description' => 'nullable|string',
        'salle_id' => 'nullable|exists:salles,id',
        'matiere_id' => 'required|exists:matieres,id',
        'filiere_id' => 'required|exists:filieres,id',
        'niveau_id' => 'required|exists:niveaux,id',
        'enseignant_id' => 'required|exists:enseignants,id'
    ]);

    // Création directe
    $session = \App\Models\Session::create($validated);

    // Charger les relations avant de retourner
    $session->load(['matiere', 'salle', 'enseignant', 'filiere', 'niveau']);

    return response()->json($session, 201);
}


public function show($id)
{
    $session = Session::with('salle')->findOrFail($id);
    return response()->json($session);
}

public function update(Request $request, $id)
{
    $session = Session::findOrFail($id);

    $validated = $request->validate([
        'statut' => 'sometimes|required|string',
        'heure_debut' => 'sometimes|required|date',
        'heure_fin' => 'sometimes|required|date|after:heure_debut',
        'lien' => 'nullable|string',
        'description' => 'nullable|string',
        'salle_id' => 'nullable|exists:salles,id',
        'matiere_id' => 'sometimes|exists:matieres,id',
        'filiere_id' => 'sometimes|exists:filieres,id',
        'niveau_id' => 'sometimes|exists:niveaux,id',
        'enseignant_id' => 'sometimes|exists:enseignants,id',
    ]);

    $session->update($validated);

    // Recharger les relations si besoin
    $session->load(['matiere', 'salle', 'enseignant', 'filiere', 'niveau']);

    return response()->json($session);
}

public function destroy($id)
{
    $session = Session::findOrFail($id);
    $session->delete();

    return response()->json(['message' => 'Session supprimée avec succès.']);
}

// public function lancerSession(Request $request, $id)
// {
//     $session = Session::findOrFail($id);

//     // 1. Mettre à jour le statut
//     $session->statut = 'En cours';
//     $session->save();

//     // 2. Récupérer les étudiants liés à la filière et au niveau
//     $etudiants = Etudiant::where('filiere_id', $session->filiere_id)
//                         ->where('niveau_id', $session->niveau_id)
//                         ->get();

//     // 3. Créer les présences
//     foreach ($etudiants as $etudiant) {
//         Presence::create([
//             'session_id' => $session->id,
//             'etudiant_id' => $etudiant->id,
//             'statut' => 'absent'
//         ]);
       
//     }
//         Notification::send($etudiants, new SessionLancee($session));
//         return response()->json("Une notification a ete envoyer a tous les utilisateurs");
// }
public function lancerSession(Request $request, $id)
{
    $session = Session::findOrFail($id);

    $session->statut = 'En cours';
    $session->save();

    $etudiants = Etudiant::where('filiere_id', $session->filiere_id)
                        ->where('niveau_id', $session->niveau_id)
                        ->whereNotNull('device_token')
                        ->get();

    foreach ($etudiants as $etudiant) {
        Presence::firstOrCreate([
            'session_id' => $session->id,
            'etudiant_id' => $etudiant->id,
        ], [
            'statut' => 'absent'
        ]);
    }

    // 4. Envoi de la notification
    // Notification::send($etudiants, new SessionLancee($session));
    foreach ($etudiants as $etudiant) {
        $deviceToken = $etudiant->device_token;

        if ($deviceToken) {
            $title = "Session lancée";
            $body = "Votre session '{$session->matiere_id}' vient de commencer.";

            $this->firebaseService->sendNotification($deviceToken, $title, $body);
        }
    }

    return response()->json([
        'message' => 'Une notification a été envoyée à tous les étudiants concernés.',
        'etudiants_notifies' => $etudiants->pluck('id')
    ]);
}



}
