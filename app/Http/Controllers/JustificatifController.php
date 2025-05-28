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
            'presence_id' =>'required',
        ]);

        $justificatif = Justificatifs::create($request->toArray());
        return response()->json(['message' =>"votre justificatif viens d'etre envoyer" , 'Justificatif' => $justificatif]);
    }

    public function ListerLesJustificatifsParEnseignant($id){
        $justificatifs =  Justificatifs::where("enseignant_id" , $id)->where('statut', '!=', 'Accepté')->with('etudiant')->get();
        return response()->json([
            'justificatifs' => $justificatifs
        ]);
    }

    public function renvoyerUnJustificatif(Request $request , $id){
        $justificatif = Justificatifs::where("id" , $id)->first();
        $request->validate([
            'reponse_enseignant' => 'required|string|max:255',            
        ]);

        $justificatif->reponse_enseignant = $request->reponse_enseignant;
        $justificatif->statut = "Renvoyé";
        $justificatif->update();
        return response()->json(["message"=> "justificatif renvoyer" , "justificatif"=>$justificatif]);
    }
    public function ModifierUnJustificatifApresRenvoi(Request $request ,$id){
      $justificatif = Justificatifs::findOrFail($id);
      $justificatif->statut = "Nouveau";
      $justificatif->update($request->all());
      return response()->json(['message'=>'Votre justification a ete renvoyer' , 'justificatif'=>$justificatif]);
  
    }
    public function RefuserUnJustificatif($id){
        $justificatif = Justificatifs::findOrFail($id);
        $justificatif->statut = "Refusé";
        $justificatif->update();
        return response()->json(['message'=>'Votre justification a ete refusé' ]);
    
      }
}
