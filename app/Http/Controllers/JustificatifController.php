<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Justificatifs;
use Illuminate\Http\Request;

class JustificatifController extends Controller
{
    public function CreationDeJustificatif(Request $request){
        $request->validate([
            'message' => 'required|string|max:255',
            'matiere_id'=>'required',
            'etudiant_id' =>'required',
            'enseignant_id' =>'required',
        ]);

        $justificatif = Justificatifs::create($request->toArray());
        return response()->json(['message' =>"votre justificatif viens d'etre envoyer" , 'Justificatif' => $justificatif]);
    }

    public function ListerLesJustificatifsParEnseignant($id){
        $justificatifs =  Justificatifs::where("enseignant_id" , $id)->with('etudiant')->get();
        return response()->json([
            'justificatifs' => $justificatifs
        ]);
    }
}
