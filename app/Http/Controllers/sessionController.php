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

use App\Notifications\MessageNotification;
use App\Notifications\Templates\NotificationTemplate;

use App\Models\Matiere;
use App\Models\Salle;

use App\Services\FirebaseNotificationService;


class sessionController extends Controller
{
    // Ajoué per le dev du FRONT END --------------------------------------------------------------------
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

public function getUpcomingSession() {
    $session = Session::where('statut', 'À venir')
                      ->where('heure_debut', '<=', now())
                      ->first();

    return response()->json($session);
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
public function sessionsParSemaineCourante()
{
    $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY); // Lundi à 00:00
    $weekEnd = Carbon::now()->endOfWeek(Carbon::SUNDAY);     // Dimanche à 23:59

    $sessions = Session::where(function ($query) use ($weekStart, $weekEnd) {
        
        $query->whereBetween('heure_debut', [$weekStart, $weekEnd])
              ->orWhereBetween('heure_fin', [$weekStart, $weekEnd]);
    })
    ->get();

    return $sessions;
}

public function sessionParJourCourant()
{
    $todayStart = Carbon::today()->startOfDay();  // 00:00:00
    $todayEnd = Carbon::today()->endOfDay();      // 23:59:59

    $sessions = Session::where(function ($query) use ($todayStart, $todayEnd) {
        $query->whereBetween('heure_debut', [$todayStart, $todayEnd])
              ->orWhereBetween('heure_fin', [$todayStart, $todayEnd]);
    }) // Optional condition
    ->get();

    return $sessions;
}

public function filtrerSessions(Request $request)
{
    $request->validate([
        'filiere_id' => 'required|integer|exists:filieres,id',
        'niveau_id' => 'required|integer|exists:niveaux,id',
        'periode' => 'nullable|in:jour,semaine',
        'statut' => 'nullable|in:À venir,En cours,Terminée',
    ]);

    $query = Session::where('filiere_id', $request->filiere_id)
        ->where('niveau_id', $request->niveau_id);

    if ($request->periode === 'jour') {
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();
    } elseif ($request->periode === 'semaine') {
        $start = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $end = Carbon::now()->endOfWeek(Carbon::SUNDAY);
    }

    if (isset($start) && isset($end)) {
        $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('heure_debut', [$start, $end])
              ->orWhereBetween('heure_fin', [$start, $end]);
        });
    }

    if ($request->statut) {
        $query->where('statut', $request->statut);
    }

    $sessions = $query->with(['matiere', 'salle', 'enseignant', 'niveau', 'filiere'])
        ->orderBy('heure_debut')
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
//         'matiere_id' => 'sometimes|exists:matieres,id',
//         'filiere_id' => 'sometimes|exists:filieres,id',
//         'niveau_id' => 'sometimes|exists:niveaux,id',
//         'enseignant_id' => 'sometimes|exists:enseignants,id',
//     ]);

//     $session->update($validated);

//     // Recharger les relations si besoin
//     $session->load(['matiere', 'salle', 'enseignant', 'filiere', 'niveau']);

//     return response()->json($session);
// }

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
//         'matiere_id' => 'sometimes|exists:matieres,id',
//         'filiere_id' => 'sometimes|exists:filieres,id',
//         'niveau_id' => 'sometimes|exists:niveaux,id',
//         'enseignant_id' => 'sometimes|exists:enseignants,id',
//     ]);

//     $session->update($validated);

//     // Recharger les relations
//     $session->load(['matiere', 'salle', 'enseignant', 'filiere', 'niveau']);

//     // Si la session est modifiée sur une donnée importante, notifier les étudiants
//     if (isset($validated['heure_debut']) || isset($validated['salle_id']) || isset($validated['lien'])) {
//         $matiere = $session->matiere->nom ?? 'cours';
//         $heure = $session->heure_debut;
//         $sessionId = $session->id;

//         // Template de notification type 'info'
//         $template = NotificationTemplate::courseUpdated($matiere, $heure); // À créer dans ton helper

//         // Récupérer les étudiants concernés
//         $etudiants = Etudiant::where('filiere_id', $session->filiere_id)
//                              ->where('niveau_id', $session->niveau_id)
//                              ->get();

//         foreach ($etudiants as $etudiant) {
//             // 1. Notification Laravel
//             $etudiant->notify(new MessageNotification(
//                 $template['title'],
//                 $template['message'],
//                 $template['type']
//             ));

//             // 2. Notification Push
//             if ($etudiant->device_token) {
//                 $this->firebaseService->sendNotification(
//                     deviceToken: $etudiant->device_token,
//                     title: $template['title'],
//                     body: $template['message'],
//                     redirectUrl: '/notification',
//                     data: [
//                         'matiere' => $matiere,
//                         'heure' => $heure,
//                         'session_id' => $id,
//                     ],
//                     type: 'warning' // ex: 'alert', 'info', etc.
//                 );
//             }
//         }
//     }

//     return response()->json([
//         'message' => 'Session mise à jour avec succès',
//         'session' => $session
//     ]);
// }


public function update(Request $request, $id)
{
    $session = Session::with(['matiere', 'salle', 'filiere', 'niveau'])->findOrFail($id);

    // Sauvegarder l’état original
    $original = $session->replicate();

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
    $session->load(['matiere', 'salle', 'enseignant', 'filiere', 'niveau']);

    // Préparation des modifications pour le message
    $modifs = [];

    if (array_key_exists('heure_debut', $validated)) {
        $modifs[] = 'Heure : ' . ($original->heure_debut ?? '-') . ' → ' . $session->heure_debut;
    }

    if (array_key_exists('salle_id', $validated)) {
        $ancienneSalle = optional(Salle::find($original->salle_id))->nom ?? '-';
        $nouvelleSalle = optional($session->salle)->nom ?? '-';
        $modifs[] = 'Salle : ' . $ancienneSalle . ' → ' . $nouvelleSalle;
    }

    if (array_key_exists('matiere_id', $validated)) {
        $ancienneMatiere = optional(Matiere::find($original->matiere_id))->nom ?? '-';
        $nouvelleMatiere = optional($session->matiere)->nom ?? '-';
        $modifs[] = 'Matière : ' . $ancienneMatiere . ' → ' . $nouvelleMatiere;
    }

    if (array_key_exists('lien', $validated)) {
        $modifs[] = 'Lien : ' . ($original->lien ?? '-') . ' → ' . ($session->lien ?? '-');
    }

    if (!empty($modifs)) {
        $matiere = optional($session->matiere)->nom
            ?? optional(Matiere::find($original->matiere_id))->nom
            ?? 'Cours';
        $heure = $session->heure_debut ?? $original->heure_debut;
        $sessionId = $session->id;

        $details = implode(" | ", $modifs);
        $title = 'Modification du cours : ' . $matiere;
        $message = "$matiere prévu à $heure a été modifié.\n$details";

        // Étudiants concernés (après modif)
        $etudiants = Etudiant::where('filiere_id', $session->filiere_id)
                             ->where('niveau_id', $session->niveau_id)
                             ->get();

        foreach ($etudiants as $etudiant) {
            // Laravel DB notification
            $etudiant->notify(new MessageNotification(
                $title,
                $message,
                'info'
            ));

            // FCM push
            if ($etudiant->device_token) {
                try {
                    $this->firebaseService->sendNotification(
                        deviceToken: $etudiant->device_token,
                        title: $title,
                        body: $message,
                        redirectUrl: '/notification',
                        data: [
                            'matiere' => (string)$matiere,
                            'heure' => (string)$heure,
                            'session_id' => (string)$sessionId,
                            'modifs' => (string)$details,
                            'action' => 'refresh',
                        ],
                        type: 'warning'
                    );
                } catch (\Exception $e) {
                    \Log::error("Erreur d'envoi FCM pour l'étudiant {$etudiant->id} : " . $e->getMessage());
                }
            }
        }
    }

    return response()->json([
        'message' => 'Session mise à jour avec succès',
        'session' => $session
    ]);
}

// public function destroy($id)
// {
//     $session = Session::with(['matiere', 'niveau', 'filiere'])->findOrFail($id);

//     // Récupérer les infos pour la notification
//     $matiere = $session->matiere->nom ?? 'matière inconnue';
//     $heure = $session->heure_debut ?? 'heure inconnue';
//     $filiereId = $session->filiere_id;
//     $niveauId = $session->niveau_id;

//     // Supprimer la session
//     $session->delete();

//     // Récupérer les étudiants de la même filière + niveau
//     $etudiants = Etudiant::where('filiere_id', $filiereId)
//                          ->where('niveau_id', $niveauId)
//                          ->get();

//     // Créer le template
//     $template = NotificationTemplate::courseCancelled($matiere, $heure);

//     // Envoyer la notification à chaque étudiant
//     foreach ($etudiants as $etudiant) {
//         $etudiant->notify(new MessageNotification(
//             $template['title'],
//             $template['message'],
//             $template['type']
//         ));
//     }

//     return response()->json(['message' => 'Session supprimée et notifications envoyées avec succès.']);
// }

public function destroy($id)
{
    $session = Session::with(['matiere', 'niveau', 'filiere'])->findOrFail($id);

    // Récup infos
    $matiere = $session->matiere->nom ?? 'matière inconnue';
    $heure = $session->heure_debut ?? 'heure inconnue';
    $filiereId = $session->filiere_id;
    $niveauId = $session->niveau_id;

    // Supprimer la session
    $session->delete();

    // Récup les étudiants concernés
    $etudiants = Etudiant::where('filiere_id', $filiereId)
                         ->where('niveau_id', $niveauId)
                         ->get();

    // Génère le template Laravel
    $template = NotificationTemplate::courseCancelled($matiere, $heure);

    // Envoyer à chaque étudiant : DB + Push FCM
    foreach ($etudiants as $etudiant) {
        // 1. Notification Laravel (stockée en base)
        $etudiant->notify(new MessageNotification(
            $template['title'],
            $template['message'],
            $template['type']
        ));

        // 2. Notification push via FCM (si token dispo)
        if ($etudiant->device_token) {
            $this->firebaseService->sendNotification(
                deviceToken: $etudiant->device_token,
                title: $template['title'],
                body: $template['message'],
                redirectUrl: '/notification',
                data: [
                    'matiere' => $matiere,
                    'heure' => $heure,
                    'session_id' => $id,
                    'action' => 'refresh',
                ],
                type: 'alert' // ex: 'alert', 'info', etc.
            );
        }
    }

    return response()->json([
        'message' => 'Session supprimée et notifications envoyées avec succès.',
        'etudiants_notifies' => $etudiants->pluck('id')
    ]);
}



// public function destroy($id)
// {
//     $session = Session::findOrFail($id);
//     $session->delete();

//     return response()->json(['message' => 'Session supprimée avec succès.']);
// }

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
    $matiere = $session->matiere->nom ?? 'matière inconnue';
    $heure = $session->heure_debut ?? 'heure inconnue';
    $salle = $session->salle->nom ?? 'salle inconnue';


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

    foreach ($etudiants as $etudiant) {
        $deviceToken = $etudiant->device_token;

        if ($deviceToken) {
            $title = "Session lancée";
            $body = "Votre session '{$session->matiere_id}' vient de commencer.";

            //$this->firebaseService->sendNotification($deviceToken, $title, $body);
            $this->firebaseService->sendNotification(
                $deviceToken,
                $title,
                $body,
                '/student-course',
                [
                    'course' => $matiere,
                    'room' => $salle,
                    'time' => $heure,
                    'action' => 'refresh',
                ],
                'modal'
            );
        }
    }

    return response()->json([
        'message' => 'Une notification a été envoyée à tous les étudiants concernés.',
        'etudiants_notifies' => $etudiants->pluck('id')
    ]);
}

public function terminerSession(Request $request, $id)
{
    $session = Session::findOrFail($id);

    $session->statut = 'Terminé';
    $session->save();

    $matiere = $session->matiere->nom ?? 'matière inconnue';
    $heure = $session->heure_debut ?? 'heure inconnue';
    $salle = $session->salle->nom ?? 'salle inconnue';

    $etudiants = Etudiant::where('filiere_id', $session->filiere_id)
                        ->where('niveau_id', $session->niveau_id)
                        ->whereNotNull('device_token')
                        ->get();
    
    foreach ($etudiants as $etudiant) {
        $deviceToken = $etudiant->device_token;

        if ($deviceToken) {
            $title = "Session terminée";
            $body = "Votre session '{$matiere}' de '{$heure}' dans la salle '{$salle}' vient de s'achever.";

            //$this->firebaseService->sendNotification($deviceToken, $title, $body);
            $this->firebaseService->sendNotification(
                $deviceToken,
                $title,
                $body,
                '/student-course',
                [
                    'course' => $matiere,
                    'room' => $salle,
                    'time' => $heure,
                    'action' => 'refresh',
                ],
                'prompt'
            );
        }
    }

    return response()->json([
        'message' => 'La session a été marquée comme terminée.',
        'session_id' => $session->id,
        'nouveau_statut' => $session->statut
    ]);
}



}
