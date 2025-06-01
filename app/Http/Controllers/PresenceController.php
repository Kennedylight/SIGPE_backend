<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Session;
use App\Models\Etudiant;
use App\Models\Presence;
use App\Services\FirebaseNotificationService;

class PresenceController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

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

    $presence = Presence::with('session')->where('session_id', $request->session_id)
                        ->where('etudiant_id', $request->etudiant_id)
                        ->first();

    if (!$presence) {
        return response()->json([
            'message' => 'Aucune présence trouvée pour cet étudiant et cette session.',
        ], 404);
    }

    // Mise à jour du statut
    $presence->statut = $request->statut;
    $presence->save();

    $session = $presence->session;
    $matiere = $session->matiere->nom ?? 'une matière';

    // Tous les étudiants inscrits à la session (même filière/niveau)
    $etudiants = Etudiant::where('filiere_id', $session->filiere_id)
                         ->where('niveau_id', $session->niveau_id)
                         ->whereNotNull('device_token')
                         ->get();

    foreach ($etudiants as $etudiant) {
        $deviceToken = trim($etudiant->device_token);
        if (!empty($deviceToken)) {
            $this->firebaseService->sendNotification(
                $deviceToken,
                "Présence mise à jour",
                "La liste de présence pour la session de {$matiere} a été modifiée.",
                '/student-course',
                [
                    'session_id' => $session->id,
                    'action' => 'refresh_list'
                ],
                'prompt'
            );
        }
    }

    return response()->json([
        'message' => 'Statut mis à jour et notification envoyée aux étudiants.',
        'presence' => $presence
    ]);
}


    // public function changerStatut(Request $request)
    // {
    //     $request->validate([
    //         'session_id' => 'required|exists:sessions,id',
    //         'etudiant_id' => 'required|exists:etudiants,id',
    //         'statut' => 'required|in:absent,présent,en retard,excusé',
    //     ]);

    //     // Vérifie si la présence existe
    //     $presence = Presence::where('session_id', $request->session_id)
    //                         ->where('etudiant_id', $request->etudiant_id)
    //                         ->first();

    //     if ($presence) {
    //         $presence->statut = $request->statut;
    //         $presence->save();

    //         return response()->json([
    //             'message' => 'Statut mis à jour.',
    //             'presence' => $presence
    //         ]);
    //     } else {
    //         return response()->json([
    //             'message' => 'Aucune présence trouvée pour cet étudiant et cette session.',
    //         ], 404);
    //     }
    // }

    public function ListeDesSessionsManquerParEtudiant($id)
    {
        $sessionsManquees = Presence::with([
                'session.matiere',
                'session.salle',
                'session.filiere',
                'session.niveau',
                'session.enseignant'
            ])
            ->where('statut', 'absent')
            ->where('etudiant_id', $id)
            ->whereDoesntHave('justificatif')
            ->whereHas('session', function ($query) {
                $query->where('statut', 'Terminé');
            })
            ->get();

        return response()->json($sessionsManquees);
    }

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

    public function getStats(Request $request, $id)
    {
        $periode = $request->query('periode');  // jour, semaine, mois, semestre, année
        $matiereId = $request->query('matiere_id'); // facultatif

        $query = Presence::where('etudiant_id', $id)
            ->whereHas('session', function ($q) use ($matiereId, $periode) {
                if ($matiereId) {
                    $q->where('matiere_id', $matiereId);
                }

                if ($periode) {
                    $now = Carbon::now();

                    switch ($periode) {
                        case 'jour':
                            $q->whereDate('heure_debut', $now->toDateString());
                            break;
                        case 'semaine':
                            $q->whereBetween('heure_debut', [$now->startOfWeek(), $now->endOfWeek()]);
                            break;
                        case 'mois':
                            $q->whereMonth('heure_debut', $now->month)->whereYear('heure_debut', $now->year);
                            break;
                        case 'semestre':
                            $start = $now->month <= 6 ? Carbon::create($now->year, 1, 1) : Carbon::create($now->year, 7, 1);
                            $end = $now->month <= 6 ? Carbon::create($now->year, 6, 30) : Carbon::create($now->year, 12, 31);
                            $q->whereBetween('heure_debut', [$start, $end]);
                            break;
                        case 'annee':
                            $q->whereYear('heure_debut', $now->year);
                            break;
                    }
                }
            });

        $presences = $query->with('session.matiere')->get();

        return response()->json([
            'total' => $presences->count(),
            'presences' => $presences
        ]);
    }

    public function getPresencesWithSessions($id)
    {
        $presences = Presence::with('session', 'session.matiere')  // on inclut toutes les infos liées à la session
            ->where('etudiant_id', $id)
            ->get();

        return response()->json($presences);
    }

    public function getTeacherPresenceStats(Request $request)
    {
        $request->validate([
            'periode' => 'nullable|in:jour,semaine,mois,semestre,annee',
            'matiere_id' => 'nullable|exists:matieres,id'
        ]);

        $teacherId = auth()->id();
        
        $sessions = Session::with('matiere')
            ->withCount([
                'presences as present_count' => fn($q) => $q->where('statut', 'présent'),
                'presences as absent_count' => fn($q) => $q->where('statut', 'absent'),
                'presences as late_count' => fn($q) => $q->where('statut', 'en retard'),
                'presences as excused_count' => fn($q) => $q->where('statut', 'excusé')
            ])
            ->where('enseignant_id', $teacherId)
            ->when($request->matiere_id, fn($q, $matiereId) => $q->where('matiere_id', $matiereId))
            ->when($request->periode, function($q, $periode) {
                $now = Carbon::now();
                switch ($periode) {
                    case 'jour':
                        return $q->whereDate('heure_debut', $now->toDateString());
                    case 'semaine':
                        return $q->whereBetween('heure_debut', [
                            $now->startOfWeek()->toDateTimeString(),
                            $now->endOfWeek()->toDateTimeString()
                        ]);
                    case 'mois':
                        return $q->whereMonth('heure_debut', $now->month)
                                ->whereYear('heure_debut', $now->year);
                    case 'semestre':
                        return $now->month <= 6 
                            ? $q->whereMonth('heure_debut', '<=', 6)->whereYear('heure_debut', $now->year)
                            : $q->whereMonth('heure_debut', '>=', 7)->whereYear('heure_debut', $now->year);
                    case 'annee':
                        return $q->whereYear('heure_debut', $now->year);
                }
            })
            ->orderBy('heure_debut')
            ->get();

        return response()->json($sessions);
    }

    public function getStudentAttendanceStats(Request $request)
    {
        $request->validate([
            'matiere_id' => 'nullable|exists:matieres,id',
            'periode' => 'nullable|in:week,month,semester,year',
            'filiere_id' => 'nullable|exists:filieres,id',
            'niveau_id' => 'nullable|exists:niveaux,id'
        ]);

        // Récupérer tous les étudiants avec leurs présences filtrées
        $query = Etudiant::with(['presences' => function($q) use ($request) {
            $q->with('session.matiere')
            ->whereHas('session', function($q) use ($request) {
                if ($request->matiere_id) {
                    $q->where('matiere_id', $request->matiere_id);
                }
                if ($request->periode) {
                    $now = Carbon::now();
                    switch ($request->periode) {
                        case 'week':
                            $q->whereBetween('heure_debut', [$now->startOfWeek(), $now->endOfWeek()]);
                            break;
                        case 'month':
                            $q->whereMonth('heure_debut', $now->month);
                            break;
                        case 'semester':
                            $start = $now->month <= 6 
                                ? Carbon::create($now->year, 1, 1) 
                                : Carbon::create($now->year, 7, 1);
                            $end = $now->month <= 6 
                                ? Carbon::create($now->year, 6, 30) 
                                : Carbon::create($now->year, 12, 31);
                            $q->whereBetween('heure_debut', [$start, $end]);
                            break;
                        case 'year':
                            $q->whereYear('heure_debut', $now->year);
                            break;
                    }
                }
                if ($request->filiere_id) {
                    $q->where('filiere_id', $request->filiere_id);
                }
                if ($request->niveau_id) {
                    $q->where('niveau_id', $request->niveau_id);
                }
            });
        }]);

        if ($request->filiere_id) {
            $query->where('filiere_id', $request->filiere_id);
        }
        if ($request->niveau_id) {
            $query->where('niveau_id', $request->niveau_id);
        }

        $students = $query->get()->map(function($student) {
            $totalSessions = $student->presences->count();
            $presentCount = $student->presences->where('statut', 'présent')->count();
            $lateCount = $student->presences->where('statut', 'en retard')->count();
            $excusedCount = $student->presences->where('statut', 'excusé')->count();
            $absentCount = $student->presences->where('statut', 'absent')->count();

            $attendanceRate = $totalSessions > 0 
                ? round(($presentCount + $lateCount * 0.5 + $excusedCount * 0.8) / $totalSessions * 100)
                : 0;

            return [
                'id' => $student->id,
                'matricule' => $student->matricule,
                'nom' => $student->nom,
                'prenom' => $student->prenom,
                'total_sessions' => $totalSessions,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'excused_count' => $excusedCount,
                'absent_count' => $absentCount,
                'attendance_rate' => $attendanceRate
            ];
        });

        return response()->json($students);
    }

    public function getTeacherPresenceStats_2(Request $request)
{
    $request->validate([
        'periode' => 'nullable|in:jour,semaine,mois,semestre,annee',
        'matiere_id' => 'nullable|exists:matieres,id'
    ]);

    $teacherId = auth()->id();
    
    $sessions = Session::with(['matiere', 'presences.etudiant'])
        ->withCount([
            'presences as present_count' => fn($q) => $q->where('statut', 'présent'),
            'presences as absent_count' => fn($q) => $q->where('statut', 'absent'),
            'presences as late_count' => fn($q) => $q->where('statut', 'en retard'),
            'presences as excused_count' => fn($q) => $q->where('statut', 'excusé')
        ])
        ->where('enseignant_id', $teacherId)
        // ... le reste de votre méthode existante ...
        ->get();

    return response()->json($sessions);
}

    public function getAssiduiteParEtudiant(Request $request, $enseignantId)
{
    $matiereId = $request->query('matiere_id');
    $periode = $request->query('periode');
    $filiereId = $request->query('filiere_id');
    $niveauId = $request->query('niveau_id');

    if (!$matiereId) {
        return response()->json(['message' => 'ID de matière requis.'], 400);
    }

    $sessions = \App\Models\Session::with(['presences.etudiant'])
        ->where('enseignant_id', $enseignantId)
        ->where('matiere_id', $matiereId)
        ->when($filiereId, fn($q) => $q->where('filiere_id', $filiereId))
        ->when($niveauId, fn($q) => $q->where('niveau_id', $niveauId))
        ->when($periode, function ($query) use ($periode) {
            $now = Carbon::now();
            switch ($periode) {
                case 'jour':
                    $query->whereDate('heure_debut', $now);
                    break;
                case 'semaine':
                    $query->whereBetween('heure_debut', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'mois':
                    $query->whereMonth('heure_debut', $now->month)->whereYear('heure_debut', $now->year);
                    break;
                case 'semestre':
                    $start = $now->month <= 6 ? Carbon::create($now->year, 1, 1) : Carbon::create($now->year, 7, 1);
                    $end = $now->month <= 6 ? Carbon::create($now->year, 6, 30) : Carbon::create($now->year, 12, 31);
                    $query->whereBetween('heure_debut', [$start, $end]);
                    break;
                case 'annee':
                    $query->whereYear('heure_debut', $now->year);
                    break;
            }
        })->get();

    $assiduiteParEtudiant = [];

    foreach ($sessions as $session) {
        foreach ($session->presences as $presence) {
            $etudiant = $presence->etudiant;
            if (!$etudiant) continue;

            $id = $etudiant->id;

            if (!isset($assiduiteParEtudiant[$id])) {
                $assiduiteParEtudiant[$id] = [
                    'etudiant_id' => $id,
                    'matricule' => $etudiant->matricule,
                    'nom' => $etudiant->nom,
                    'prenom' => $etudiant->prenom,
                    'total' => 0,
                    'present' => 0
                ];
            }

            $assiduiteParEtudiant[$id]['total']++;
            $statut = strtolower($presence->statut);

            if (in_array($statut, ['présent', 'present', 'en retard', 'excusé'])) {
                $assiduiteParEtudiant[$id]['present']++;
            }
        }
    }

    $final = array_map(function ($item) {
        $item['taux'] = $item['total'] > 0 ? round(($item['present'] / $item['total']) * 100) : 0;
        return $item;
    }, array_values($assiduiteParEtudiant));

    return response()->json($final);
}

// function getDateRangeFromPeriode(?string $periode): ?array
// {
//     if (!$periode || $periode === 'all') return null;

//     $now = Carbon::now();

//     switch ($periode) {
//         case 'jour':
//             return [
//                 $now->copy()->startOfDay(),
//                 $now->copy()->endOfDay()
//             ];

//         case 'semaine':
//             return [
//                 $now->copy()->startOfWeek(),
//                 $now->copy()->endOfWeek()
//             ];

//         case 'mois':
//             return [
//                 $now->copy()->startOfMonth(),
//                 $now->copy()->endOfMonth()
//             ];

//         case 'semestre':
//             if ($now->month <= 6) {
//                 return [
//                     Carbon::create($now->year, 1, 1)->startOfDay(),
//                     Carbon::create($now->year, 6, 30)->endOfDay()
//                 ];
//             } else {
//                 return [
//                     Carbon::create($now->year, 7, 1)->startOfDay(),
//                     Carbon::create($now->year, 12, 31)->endOfDay()
//                 ];
//             }

//         case 'annee':
//             return [
//                 Carbon::create($now->year, 1, 1)->startOfDay(),
//                 Carbon::create($now->year, 12, 31)->endOfDay()
//             ];

//         default:
//             return null;
//     }
// }

// public function getTauxPresenceParCritere(Request $request)
// {
//     $request->validate([
//         'periode' => 'required|in:jour,semaine,mois,semestre,annee',
//         'critere' => 'required|in:enseignant,etudiant,salle,filiere,niveau'
//     ]);

//     $periode = $request->periode;
//     $critere = $request->critere;

//     $now = Carbon::now();
//     $start = null;
//     $end = null;

//     switch ($periode) {
//         case 'jour':
//             $start = $now->copy()->startOfDay();
//             $end = $now->copy()->endOfDay();
//             break;
//         case 'semaine':
//             $start = $now->copy()->startOfWeek();
//             $end = $now->copy()->endOfWeek();
//             break;
//         case 'mois':
//             $start = $now->copy()->startOfMonth();
//             $end = $now->copy()->endOfMonth();
//             break;
//         case 'semestre':
//             $start = $now->month <= 6 ? Carbon::create($now->year, 1, 1) : Carbon::create($now->year, 7, 1);
//             $end = $now->month <= 6 ? Carbon::create($now->year, 6, 30) : Carbon::create($now->year, 12, 31);
//             break;
//         case 'annee':
//             $start = Carbon::create($now->year, 1, 1);
//             $end = Carbon::create($now->year, 12, 31);
//             break;
//     }

//     // Récupérer toutes les présences dans la période, avec sessions et le critère
//     $presences = Presence::with('session')
//         ->whereHas('session', function ($query) use ($start, $end) {
//             $query->whereBetween('heure_debut', [$start, $end]);
//         })
//         ->get();

//     // Groupement des présences par critère demandé
//     $grouped = $presences->groupBy(function ($presence) use ($critere) {
//         return optional($presence->session)->{$critere . '_id'};
//     });

//     // Calcul du taux de présence par groupe
//     $results = [];

//     foreach ($grouped as $id => $presGroup) {
//         $total = $presGroup->count();
//         $presents = $presGroup->where('statut', 'présent')->count();
//         $taux = $total > 0 ? round(($presents / $total) * 100, 2) : 0;

//         $results[] = [
//             'id' => $id,
//             'total_presences' => $total,
//             'nombre_presents' => $presents,
//             'taux_presence' => $taux
//         ];
//     }

//     return response()->json([
//         'periode' => $periode,
//         'critere' => $critere,
//         'resultats' => $results
//     ]);
// }

public function getAttendanceStats(Request $request)
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'enseignant_id' => 'nullable|exists:enseignants,id',
        'etudiant_id' => 'nullable|exists:etudiants,id',
        'salle_id' => 'nullable|exists:salles,id',
        'filiere_id' => 'nullable|exists:filieres,id',
        'niveau_id' => 'nullable|exists:niveaux,id'
    ]);

    $query = Presence::with(['session', 'etudiant'])
        ->whereHas('session', function($q) use ($request) {
            $q->whereBetween('heure_debut', [$request->start_date, $request->end_date])
              ->when($request->enseignant_id, fn($q, $id) => $q->where('enseignant_id', $id))
              ->when($request->salle_id, fn($q, $id) => $q->where('salle_id', $id))
              ->when($request->filiere_id, fn($q, $id) => $q->where('filiere_id', $id))
              ->when($request->niveau_id, fn($q, $id) => $q->where('niveau_id', $id));
        })
        ->when($request->etudiant_id, fn($q, $id) => $q->where('etudiant_id', $id));

    $total = $query->count();
    $stats = $query->selectRaw('statut, count(*) as count')
                 ->groupBy('statut')
                 ->pluck('count', 'statut');

    return response()->json([
        'total' => $total,
        'present' => $stats['présent'] ?? 0,
        'absent' => $stats['absent'] ?? 0,
        'late' => $stats['en retard'] ?? 0,
        'excused' => $stats['excusé'] ?? 0,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date
    ]);
}

public function getGlobalPresenceStats(Request $request)
{
    $request->validate([
        'periode' => 'nullable|in:jour,semaine,mois,semestre,annee',
        'filiere_id' => 'nullable|exists:filieres,id',
        'niveau_id' => 'nullable|exists:niveaux,id'
    ]);

    $enseignantId = auth()->id();
    $periode = $request->periode;

    $range = $this->getDateRangeFromPeriode($periode);

    // Récupérer toutes les sessions concernées
    $sessions = Session::with('presences')
        ->where('enseignant_id', $enseignantId)
        ->when($request->filiere_id, fn($q) => $q->where('filiere_id', $request->filiere_id))
        ->when($request->niveau_id, fn($q) => $q->where('niveau_id', $request->niveau_id))
        ->when($range, fn($q) => $q->whereBetween('heure_debut', $range))
        ->get();

    $stats = [
        'present' => 0,
        'absent' => 0,
        'en_retard' => 0,
        'excuse' => 0
    ];

    foreach ($sessions as $session) {
        foreach ($session->presences as $presence) {
            $statut = strtolower($presence->statut);
            match ($statut) {
                'présent', 'present'      => $stats['present']++,
                'absent'                  => $stats['absent']++,
                'en retard'               => $stats['en_retard']++,
                'excusé', 'excuse'        => $stats['excuse']++,
                default                   => null
            };
        }
    }

    return response()->json($stats);
}

private function getDateRangeFromPeriode(?string $periode): ?array
{
    if (!$periode || $periode === 'all') return null;

    $now = \Carbon\Carbon::now();

    return match ($periode) {
        'jour' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        'semaine' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
        'mois' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        'semestre' => $now->month <= 6
            ? [Carbon::create($now->year, 1, 1), Carbon::create($now->year, 6, 30)->endOfDay()]
            : [Carbon::create($now->year, 7, 1), Carbon::create($now->year, 12, 31)->endOfDay()],
        'annee' => [Carbon::create($now->year, 1, 1), Carbon::create($now->year, 12, 31)->endOfDay()],
        default => null
    };
}

public function getStatutEtudiant(Request $request)
{
    $request->validate([
        'matricule' => 'required|string',
        'matiere_id' => 'required|exists:matieres,id',
        'date' => 'required|date',
    ]);

    $etudiant = \App\Models\Etudiant::where('matricule', $request->matricule)->first();

    if (!$etudiant) {
        return response()->json(['message' => 'Étudiant introuvable.'], 404);
    }

    $session = \App\Models\Session::where('matiere_id', $request->matiere_id)
        ->whereDate('heure_debut', $request->date)
        ->first();

    if (!$session) {
        return response()->json(['message' => 'Aucune session trouvée pour cette date.'], 404);
    }

    $presence = \App\Models\Presence::where('etudiant_id', $etudiant->id)
        ->where('session_id', $session->id)
        ->first();

    return response()->json([
        'etudiant' => $etudiant->only(['id', 'nom', 'prenom', 'matricule']),
        'session' => $session->only(['id', 'heure_debut', 'heure_fin']),
        'statut' => $presence ? $presence->statut : 'Non marqué'
    ]);
}

public function getStatistiquesEtudiantParMatiere(Request $request)
{
    $request->validate([
        'etudiant_id' => 'required|exists:etudiants,id',
        'matiere_id' => 'required|exists:matieres,id',
    ]);

    $presences = Presence::with(['session'])
        ->where('etudiant_id', $request->etudiant_id)
        ->whereHas('session', function ($query) use ($request) {
            $query->where('matiere_id', $request->matiere_id)
                  ->where('heure_debut', '<', now()); // uniquement les sessions passées
        })
        ->orderByDesc('session_id')
        ->get();

    $resultats = $presences->map(function ($presence) {
        return [
            'session_id'   => $presence->session_id,
            'date'         => optional($presence->session)->heure_debut,
            'statut'       => strtolower($presence->statut),
            'justificatif' => $presence->justificatif ?? null
        ];
    });

    return response()->json($resultats);
}

public function getStatsBySession($sessionId)
{
    $session = \App\Models\Session::withCount([
        'presences as present' => fn($q) => $q->where('statut', 'présent'),
        'presences as absent' => fn($q) => $q->where('statut', 'absent'),
        'presences as en_retard' => fn($q) => $q->where('statut', 'en retard'),
        'presences as excuse' => fn($q) => $q->where('statut', 'excusé'),
    ])->find($sessionId);

    if (!$session) {
        return response()->json(['message' => 'Session non trouvée'], 404);
    }

    return response()->json([
        'session_id' => $sessionId,
        'present' => $session->present,
        'absent' => $session->absent,
        'en_retard' => $session->en_retard,
        'excuse' => $session->excuse
    ]);
}


}