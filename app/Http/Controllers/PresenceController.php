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
}
