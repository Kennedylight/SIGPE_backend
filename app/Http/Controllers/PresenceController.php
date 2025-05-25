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

}
