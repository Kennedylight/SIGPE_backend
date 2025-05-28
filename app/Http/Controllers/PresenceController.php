<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Session;
use App\Models\Etudiant;
use App\Models\Presence;

class PresenceController extends Controller
{
    public function getInscritsParSession($id)
    {
        $presences = Presence::with('etudiant')
            ->where('session_id', $id)
            ->get();

        return response()->json($presences);
    }
    public function changerStatut(Request $request)
{
    $request->validate([
        'session_id' => 'required|exists:sessions,id',
        'etudiant_id' => 'required|exists:etudiants,id',
        'statut' => 'required|in:absent,présent,en retard,excusé',
    ]);

    // Vérifie si la présence existe
    $presence = Presence::where('session_id', $request->session_id)
                        ->where('etudiant_id', $request->etudiant_id)
                        ->first();

    if ($presence) {
        $presence->statut = $request->statut;
        $presence->save();

        return response()->json([
            'message' => 'Statut mis à jour.',
            'presence' => $presence
        ]);
    } else {
        return response()->json([
            'message' => 'Aucune présence trouvée pour cet étudiant et cette session.',
        ], 404);
    }
}

// public function ajouterPresence(Request $request)
// {
//     $request->validate([
//         'session_id' => 'required|exists:sessions,id',
//         'matricule' => 'required|string',
//         'statut' => 'required|in:absent,présent,en retard,excusé',
//     ]);

//     // Recherche de l'étudiant par matricule
//     $etudiant = Etudiant::where('matricule', $request->matricule)->first();

//     if (!$etudiant) {
//         return response()->json([
//             'message' => 'Aucun étudiant trouvé avec ce matricule.',
//         ], 404);
//     }

//     // Vérifie si une présence existe déjà
//     $exists = Presence::where('session_id', $request->session_id)
//                       ->where('etudiant_id', $etudiant->id)
//                       ->exists();

//     if ($exists) {
//         return response()->json([
//             'message' => 'Présence déjà enregistrée pour cet étudiant dans cette session.',
//         ], 409);
//     }

//     // Création de la présence
//     $presence = Presence::create([
//         'session_id' => $request->session_id,
//         'etudiant_id' => $etudiant->id,
//         'statut' => $request->statut,
//     ]);

//     return response()->json([
//         'message' => 'Présence ajoutée avec succès.',
//         'presence' => $presence
//     ], 201);
// }

public function ajouterPresence(Request $request)
{
    $request->validate([
        'session_id' => 'required|exists:sessions,id',
        'matricule' => 'required|string',
        'statut' => 'required|in:absent,présent,en retard,excusé',
    ]);

    // Recherche de l'étudiant par matricule
    $etudiant = Etudiant::where('matricule', $request->matricule)->first();

    if (!$etudiant) {
        return response()->json([
            'message' => 'Aucun étudiant trouvé avec ce matricule.',
        ], 404);
    }

    // Récupération de la session
    $session = Session::find($request->session_id);
    if (!$session) {
        return response()->json([
            'message' => 'Session introuvable.',
        ], 404);
    }

    // Vérifie la correspondance entre la session et l'étudiant
    if ($etudiant->filiere_id !== $session->filiere_id || $etudiant->niveau_id !== $session->niveau_id) {
        return response()->json([
            'message' => 'L’étudiant ne correspond pas à la filière ou au niveau de cette session.',
        ], 403);
    }

    // Vérifie s’il y a déjà une présence enregistrée
    $exists = Presence::where('session_id', $request->session_id)
                      ->where('etudiant_id', $etudiant->id)
                      ->exists();

    if ($exists) {
        return response()->json([
            'message' => 'Présence déjà enregistrée pour cet étudiant dans cette session.',
        ], 409);
    }

    // Création de la présence
    $presence = Presence::create([
        'session_id' => $request->session_id,
        'etudiant_id' => $etudiant->id,
        'statut' => $request->statut,
    ]);

    return response()->json([
        'message' => 'Présence ajoutée avec succès.',
        'presence' => $presence
    ], 201);
}



}
