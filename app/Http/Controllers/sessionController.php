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

class sessionController extends Controller
{
    // Ajoué per le dev du FRONT END --------------------------------------------------------------------
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


// public function store(Request $request)
// {
//     $validated = $request->validate([
//         'statut' => 'required|string',
//         'heure_debut' => 'required|date',
//         'heure_fin' => 'required|date|after:heure_debut',
//         'lien' => 'nullable|string',
//         'description' => 'nullable|string',
//         'salle_id' => 'nullable|exists:salles,id',
//         'matiere_id' => 'required|exists:matieres,id',
//         'filiere_id' => 'required|exists:filieres,id',
//         'niveau_id' => 'required|exists:niveaux,id',
//         'enseignant_id' => 'required|exists:enseignants,id'
//     ]);

//     // $validated = $request->validate([
//     //     'statut' => 'required|string',
//     //     'heure_debut' => 'required|date',
//     //     'heure_fin' => 'required|date|after:heure_debut',
//     //     'lien' => 'nullable|string',
//     //     'description' => 'nullable|string',
//     //     'salle_id' => 'nullable|exists:salles,id',
//     //     'matiere_id' => 'required|exists:matieres,id',
//     //     'filiere_id' => 'required|exists:filieres,id',  // validation sur table avec faute
//     //     'niveau_id' => 'required|exists:niveaux,id',
//     //     'enseignant_id' => 'required|exists:enseignants,id'
//     // ]);

//     // Corriger le nom de la clé pour correspondre à la colonne de la table sessions
//     //$validated['filiere_id'] = $validated['filiere_id'];
//     //unset($validated['filiere_id']);

//     // Créer la session
//     $session = \App\Models\Session::create($validated);

//     // Attacher les relations many-to-many
//     //$session->enseignants()->attach($validated['enseignant_id']);
//     //$session->filieres()->attach($validated['filiere_id']); // attention ici aussi on utilise la faute de frappe
//     //$session->niveaux()->attach($validated['niveau_id']);
//     $session->load(['matiere', 'salle', 'enseignant', 'filiere', 'niveau']);

//     return response()->json($session, 201);
// }



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

// public function update(Request $request, $id)
// {
//     $session = Session::findOrFail($id);

//     $validated = $request->validate([
//         'statut' => 'sometimes|required|string',
//         'heure_debut' => 'sometimes|required|date',
//         'heure_fin' => 'sometimes|required|date|after:heure_debut',
//         'lien' => 'nullable|string',
//         'description' => 'nullable|string',
//         'salle_id' => 'nullable|exists:salles,id',
//     ]);

//     $session->update($validated);

//     return response()->json($session);
// }

public function destroy($id)
{
    $session = Session::findOrFail($id);
    $session->delete();

    return response()->json(['message' => 'Session supprimée avec succès.']);
}

public function lancerSession(Request $request, $id)
{
    $session = Session::findOrFail($id);

    // 1. Mettre à jour le statut
    $session->statut = 'En cours';
    $session->save();

    // 2. Récupérer les étudiants liés à la filière et au niveau
    $etudiants = Etudiant::where('filiere_id', $session->filiere_id)
                        ->where('niveau_id', $session->niveau_id)
                        ->get();

    // 3. Créer les présences
    foreach ($etudiants as $etudiant) {
        Presence::create([
            'session_id' => $session->id,
            'etudiant_id' => $etudiant->id,
            'statut' => 'absent'
        ]);

        // 4. Émettre une notification
        $this->envoyerNotificationEtudiant($etudiant, "Votre cours de {$session->matiere->nom} vient de commencer.");
    }

    return response()->json(['message' => 'Session lancée avec succès']);
}

public function envoyerNotificationEtudiant(Etudiant $etudiant, $message)
{
    if (!$etudiant->device_token) return;

    $SERVER_API_KEY = env('FCM_SERVER_KEY');

    $data = [
        "to" => $etudiant->device_token,
        "notification" => [
            "title" => "Nouveau cours",
            "body" => $message,
            "sound" => "default"
        ]
    ];

    // Chemin absolu vers cacert.pem
    $certPath = base_path('certs/cacert.pem'); // ⚠️ Assure-toi que ce fichier existe ici

    $client = new \GuzzleHttp\Client([
        'verify' => $certPath, // Cette ligne corrige l'erreur cURL 60
    ]);

    try {
        $response = $client->post("https://fcm.googleapis.com/fcm/send", [
            'headers' => [
                'Authorization' => 'key=' . $SERVER_API_KEY,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data),
        ]);

        // (Optionnel) pour debug ou journaliser la réponse
        // Log::info('Notification envoyée: ' . $response->getBody());
    } catch (\Exception $e) {
        Log::error('Erreur envoi notification FCM : ' . $e->getMessage());
    }
}


// public function envoyerNotificationEtudiant(Etudiant $etudiant, $message)
// {
//     if (!$etudiant->device_token) return;

//     $SERVER_API_KEY = env('FCM_SERVER_KEY');

//     $data = [
//         "to" => $etudiant->device_token,
//         "notification" => [
//             "title" => "Nouveau cours",
//             "body" => $message,
//             "sound" => "default"
//         ]
//     ];

//     $client = new \GuzzleHttp\Client();
//     $response = $client->post("https://fcm.googleapis.com/fcm/send", [
//         'headers' => [
//             'Authorization' => 'key=' . $SERVER_API_KEY,
//             'Content-Type' => 'application/json',
//         ],
//         'body' => json_encode($data),
//     ]);
// }


}
