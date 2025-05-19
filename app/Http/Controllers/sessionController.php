<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
    ]);

    $session->update($validated);

    return response()->json($session);
}

public function destroy($id)
{
    $session = Session::findOrFail($id);
    $session->delete();

    return response()->json(['message' => 'Session supprimée avec succès.']);
}

}
