<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\Request;

class sessionController extends Controller
{
    public function sessionsParFiliereEtNiveau($filiereId, $niveauId)
{
    $sessions = Session::whereHas('filieres', function ($q) use ($filiereId) {
                        $q->where('filieres.id', $filiereId);
                    })
                    ->whereHas('niveaux', function ($q) use ($niveauId) {
                        $q->where('niveaux.id', $niveauId);
                    })
                    ->get();

    return response()->json($sessions);
}
public function index()
{
    return response()->json(Session::with('salle')->get());
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
    ]);

    $session = Session::create($validated);

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
