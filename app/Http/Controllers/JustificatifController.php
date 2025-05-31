<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Justificatifs;
use Illuminate\Http\Request;
use App\Models\Presence;

use App\Services\FirebaseNotificationService; 

class JustificatifController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function CreationDeJustificatif(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
            'matiere_id' => 'required',
            'etudiant_id' => 'required',
            'enseignant_id' => 'required',
            'presence_id' => 'required',
            'piece_jointe' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $presence = Presence::with('session')->find($request->presence_id);

        if (!$presence || $presence->session->statut !== 'Terminé') {
            return response()->json([
                'message' => "Vous ne pouvez créer un justificatif que pour une session terminée."
            ], 403);
        }

        $data = $request->only(['message', 'matiere_id', 'presence_id', 'enseignant_id']);
        $data['etudiant_id'] = auth('etudiant-api')->id();
        $data['statut'] = 'En cours';

        if ($request->hasFile('piece_jointe')) {
            $data['piece_jointes'] = $request->file('piece_jointe')->store('justificatifs');
        }

        $justificatif = Justificatifs::create($data);

        $enseignant = Enseignant::find($request->enseignant_id);
        if ($enseignant && $enseignant->device_token) {
            $title = "Nouveau justificatif reçu";
            $body = "Un étudiant vous a envoyé un justificatif pour une absence.";

            $this->firebaseService->sendNotification(
                $enseignant->device_token,
                $title,
                $body,
                '/teacher-justificatifs',
                [
                    'justificatif_id' => $justificatif->id,
                    'action' => 'refresh',
                    //'context' => 'justificatif',
                ],
                'info'
            );
        }

        return response()->json([
            'message' => "Votre justificatif vient d’être envoyé.",
            'Justificatif' => $justificatif
        ], 201);
    }

    // public function CreationDeJustificatif(Request $request){
    //     $request->validate([
    //         'message' => 'required|string|max:255',
    //         'matiere_id'=>'required',
    //         'etudiant_id' =>'required',
    //         'enseignant_id' =>'required',
    //         'presence_id' =>'required',
    //         'piece_jointe' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
    //     ]);

    //     $data = $request->only(['message', 'matiere_id', 'presence_id', 'enseignant_id']);
    //     $data['etudiant_id'] = auth('etudiant-api')->id();
    //     // $data['statut'] = 'en_attente'; // Plutôt que "Nouveau"

    //     if ($request->hasFile('piece_jointe')) {
    //         $data['piece_jointes'] = $request->file('piece_jointe')->store('justificatifs');
    //     }

    //     // $justificatif = Justificatifs::create($request->toArray());
    //     $justificatif = Justificatifs::create($data);
    //     return response()->json(['message' =>"votre justificatif viens d'etre envoyer" , 'Justificatif' => $justificatif], 201);
    // }

    public function ListerLesJustificatifsParEnseignant($id){
        $justificatifs =  Justificatifs::where("enseignant_id" , $id)->with('etudiant', 'matiere', 'presence.session')->get(); // ->where('statut', '!=', 'Accepté')
        return response()->json([
            'justificatifs' => $justificatifs
        ]);
    }

    public function ListerJustificatifsEtudiant()
    {
        $justificatifs = Justificatifs::where('etudiant_id', auth('etudiant-api')->id())
                                    ->with(['matiere', 'enseignant', 'presence.session'])
                                    ->orderBy('created_at', 'desc')
                                    ->get();

        return response()->json(['justificatifs' => $justificatifs]);
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

    // public function ModifierUnJustificatifApresRenvoi(Request $request ,$id){
    //   $justificatif = Justificatifs::findOrFail($id);
    //   $justificatif->statut = "En cours";
    //   $justificatif->update($request->all());
    //   return response()->json(['message'=>'Votre justification a ete renvoyer' , 'justificatif'=>$justificatif]);
    // }

    public function ModifierUnJustificatifApresRenvoi(Request $request, $id)
    {
        $justificatif = Justificatifs::findOrFail($id);

        // On force toujours le statut à "En cours" si c'est un renvoi étudiant
        if ($request->utilisateur === 'ETU') {
            if ($request->has('message')) {
                $justificatif->message = $request->message;
            }

            if ($request->hasFile('piece_jointe')) {
                // Sauvegarde de la pièce jointe
                $path = $request->file('piece_jointe')->store('justificatifs', 'public');
                $justificatif->piece_jointes = $path;
            }

            $justificatif->statut = "En cours";
        }

        // Si c'est un enseignant qui modifie
        if ($request->utilisateur === 'ENS') {
            if ($request->has('reponse_enseignant')) {
                $justificatif->reponse_enseignant = $request->reponse_enseignant;
            }

            if ($request->has('statut')) {
                $justificatif->statut = $request->statut;

                // Si acceptation : présence passe à "Excusé"
                if ($request->statut === 'Accepté' && $justificatif->presence) {
                    $justificatif->presence->statut = "Excusé";
                    $justificatif->presence->save();
                }
            }
        }

        $justificatif->save();

        if ($request->utilisateur === 'ETU') {
            $enseignant = $justificatif->enseignant;
            if ($enseignant && $enseignant->device_token) {
                $this->firebaseService->sendNotification(
                    $enseignant->device_token,
                    "Justificatif renvoyé",
                    "Un étudiant a renvoyé un justificatif avec modifications.",
                    '/teacher-justificatifs',
                    [
                        'justificatif_id' => $justificatif->id,
                        'action' => 'refresh',
                        //'context' => 'justificatif',
                    ],
                    'info'
                );
            }
        }

        if ($request->utilisateur === 'ENS') {
            $etudiant = $justificatif->etudiant;
            if ($etudiant && $etudiant->device_token) {
                $this->firebaseService->sendNotification(
                    $etudiant->device_token,
                    "Justificatif mis à jour",
                    "L'enseignant a modifié le statut de votre justificatif.",
                    '/student-justificatifs',
                    [
                        'justificatif_id' => $justificatif->id,
                        'action' => 'refresh',
                        //'context' => 'justificatif',
                    ],
                    'info'
                );
            }
        }

        return response()->json([
            'message' => 'Justificatif mis à jour avec succès',
            'justificatif' => $justificatif
        ]);
    }


    public function RefuserUnJustificatif($id){
        $justificatif = Justificatifs::findOrFail($id);
        $justificatif->statut = "Refusé";
        $justificatif->update();
        return response()->json(['message'=>'Votre justification a ete refusé' ]);
    }

    public function AccepterUnJustification($id){
        $justificatif = Justificatifs::where('id',$id)->with("etudiant" ,"presence")->first();
        $justificatif->statut = "Accepté";
        $justificatif->presence->statut = "présent";
        $justificatif->update();
        if ($justificatif->presence) {
            $justificatif->presence->statut = "présent";
            $justificatif->presence->save(); 
        }
        return response()->json($justificatif);
    }

    public function repondreJustificatif(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|string|in:Accepté,Refusé,Renvoyé,En cours',
            'reponse_enseignant' => 'nullable|string',
        ]);

        $justificatif = Justificatifs::with('presence')->findOrFail($id);

        $justificatif->statut = $request->statut;
        $justificatif->reponse_enseignant = $request->reponse_enseignant;

        // Si le statut est "Accepté", on modifie la présence à "excusé"
        if ($request->statut === 'Accepté' && $justificatif->presence) {
            $justificatif->presence->statut = 'excusé';
            $justificatif->presence->save();
        }

        $justificatif->save();

        $etudiant = $justificatif->etudiant;
        if ($etudiant && $etudiant->device_token) {
            $title = "Réponse à votre justificatif";
            $body = "Votre justificatif a été traité : " . $request->statut;

            $this->firebaseService->sendNotification(
                deviceToken: $etudiant->device_token,
                title: $template['title'],
                body: $template['message'],
                redirectUrl: '/student-justificatif',
                data: [
                    'justificatif_id' => $justificatif->id,
                    'action' => 'refresh',
                ],
                type: 'info'
            );
        }

        return response()->json([
            'message' => 'Réponse enregistrée avec succès.',
            'justificatif' => $justificatif
        ]);
    }

    public function supprimerJustificatif($id)
    {
        $justificatif = Justificatifs::findOrFail($id);
        
        // Si tu veux aussi supprimer la présence associée :
        // $justificatif->presence()->delete();

        $justificatif->delete();

        $etudiant = $justificatif->etudiant;
        if ($etudiant && $etudiant->device_token) {
            $title = "Justificatif supprimé";
            $body = "Un justificatif concernant votre absence a été supprimé.";

            $this->firebaseService->sendNotification(
                $etudiant->device_token,
                $title,
                $body,
                '/student-justificatifs',
                [
                    'action' => 'refresh',
                    //'context' => 'justificatif',
                ],
                'warning'
            );
        }

        return response()->json([
            'message' => 'Justificatif supprimé avec succès.'
        ]);
    }


}
