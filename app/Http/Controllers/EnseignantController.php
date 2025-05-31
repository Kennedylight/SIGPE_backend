<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EnseignantImport;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class EnseignantController extends Controller
{
    public function login(Request $request){
        $validator  = Validator::make($request->all(), [
            "matricule" => "required|string||max:255",
            "password" => "required|string|max:160",
        ]);
        if ($validator->fails()) {
            return response(["errors" => $validator->errors()->all()], 422);
        }
        $enseignant = Enseignant::where("matricule", $request->matricule)->first();
        if($enseignant){
            if(Hash::check($request->password , $enseignant->password)){
                $token = $enseignant->createToken("Laravel Password Grant Client")->accessToken;
                $response = ["token" => $token];
                return response(['access_token' => $token , "enseignant"=>$enseignant]);
            }
            else{
                $response = ["errors"=>["mot de passe incorrect"]];
                return Response()->json($response,422);
            }

        }
        else{
            $response = ["errors" => ["ce compte n\'existe pas"]];
            return Response()->json($response, 422);
        }
        $response = ["errors" => ["mot de passe incorrect"]];
        return Response()->json($response, 200);
    }
    
//creer un enseignants
    public function store(Request $request)
    {

        $validator  = Validator::make($request->all(),[
            "nom"=>"required|string|max:160",
            "email"=>"required|string|email|max:255|unique:enseignants",
            "matricule"=>"required|string||max:255|unique:enseignants",
            "password"=>"required|string|max:160",
        ]);
        if($validator->fails()){
            return response(["errors"=>$validator->errors()->all()],422);
        }
        $request['password'] = Hash::make($request["password"]);
        $request["remember_token"] = Str::random(10);
        $enseignant = enseignant::create($request->toArray());
        $token = $enseignant->createToken("Laravel Password Grant Client")->accessToken;

        return response()->json(["access_token" =>$token , "enseignant" =>$enseignant]);
    }

//import enseignant

public function ImportExcel(Request $request)
    {
    $request->validate([
        'fichier' => 'required|file|mimes:xlsx,xls,csv'
    ]);

    Excel::import(new EnseignantImport, $request->file('fichier'));
    return response()->json(['message' => 'Importation réussie !'], 200);

    }

    //recuperer les sessions de tout les enseignants

    // Ajoutés par le dev du FRONT END ---------------------------------------------------------------------
    public function show(Request $request)
    {
        $user = auth('enseignant-api')->user();

        if (!$user || !$user->enseignant) {
            return response()->json(['message' => 'Utilisateur enseignant non trouvé'], 404);
        }

        return response()->json([
            'enseignant' => $user->enseignant,
            'access_token' => $request->bearerToken()
        ]);
    }

    public function sessionsParEnseignant($id)
    {
        // Vérifie si l'enseignant existe
        $enseignant = Enseignant::findOrFail($id);

        // Récupère les sessions qui lui sont assignées
        $sessions = Session::where('enseignant_id', $id)
            ->with(['matiere', 'salle', 'enseignant', 'niveau', 'filiere'])
            ->get();

        return response()->json($sessions);
    }

    public function sessionsParEnseignantFiltrees(Request $request, $id)
{
    $request->validate([
        'periode' => 'nullable|in:jour,semaine',
        'statut' => 'nullable|in:À venir,En cours,Terminée',
    ]);

    $enseignant = Enseignant::findOrFail($id);

    $query = Session::where('enseignant_id', $id)
        ->with(['matiere', 'salle', 'enseignant', 'niveau', 'filiere']);

    // Appliquer le filtre de période (jour ou semaine)
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

    // Filtre par statut
    if ($request->statut) {
        $query->where('statut', $request->statut);
    }

    $sessions = $query->orderBy('heure_debut')->get();

    return response()->json($sessions);
}

public function updateDeviceToken(Request $request, $id) {
    $etudiant = Enseignant::findOrFail($id);
    $etudiant->device_token = $request->input('device_token');
    $etudiant->save();

    return response()->json(['message' => 'Token mis à jour']);
}

// public function sessionsParEnseignant($id)
// {
//     $enseignant = Enseignant::findOrFail($id);

//     // On récupère les sessions de cet enseignant avec les relations nécessaires
//     $sessions = Session::whereHas('enseignants', function ($query) use ($id) {
//         $query->where('enseignant_id', $id);
//     })
//     ->with(['matiere', 'salle', 'enseignants', 'niveau', 'filiere'])
//     ->get();

//     return response()->json($sessions);
// }


// Ajoutés par la dev du FRONT END --------------------------------------------------------------------

public function index()
    {
        return Enseignant::all();
    }

public function getEnseignantsByFiliereAndNiveau(Request $request)
{
    $request->validate([
        'filiere' => 'required|integer|exists:fileres,id',
        'niveau' => 'required|integer|exists:niveaux,id',
    ]);

    $filiereId = $request->filiere;
    $niveauId = $request->niveau;

    $enseignants = Enseignant::select('id', 'nom', 'prenom', 'photo')
        ->whereHas('filieres', function ($query) use ($filiereId) {
            $query->where('fileres.id', $filiereId);
        })
        ->whereHas('niveaux', function ($query) use ($niveauId) {
            $query->where('niveaux.id', $niveauId);
        })
        ->get();

    return response()->json([
        'filiere' => $filiereId,
        'niveau' => $niveauId,
        'enseignants' => $enseignants,
    ]);
}

public function modifier(Request $request)
{
    $id = $request->input('id');
    $enseignant = Enseignant::findOrFail($id);

    $request->validate([
        'nom' => 'required|string',
        'prenom' => 'required|string',
        'email' => 'required|email',
        'matricule' => 'required|string',
        'password' => 'nullable|string|min:8',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $enseignant->nom = $request->nom;
    $enseignant->prenom = $request->prenom;
    $enseignant->email = $request->email;
    $enseignant->matricule = $request->matricule;

    if ($request->filled('password')) {
        $enseignant->password = bcrypt($request->password);
    }

    if ($request->hasFile('photo')) {
        if ($enseignant->photo && Storage::disk('public')->exists($enseignant->photo)) {
            Storage::disk('public')->delete($enseignant->photo);
        }
        $photoPath = $request->file('photo')->store('enseignants', 'public');
        $enseignant->photo = $photoPath;
    }

    $enseignant->save();

    return response()->json(['message' => 'Enseignant mis à jour avec succès.']);
}

    public function getSubjects($enseignantId)
    {
        $enseignant = Enseignant::findOrFail($enseignantId);
        
        return response()->json(
            $enseignant->matieres()
                ->select('matieres.id', 'matieres.nom', 'matieres.code')
                ->get()
        );
    }

    public function getFilieres($id)
{
    $enseignant = Enseignant::with('filieres')->find($id);

    if (!$enseignant) {
        return response()->json(['message' => 'Enseignant non trouvé'], 404);
    }

    return response()->json($enseignant->filieres);
}

public function getNiveaux($id)
{
    $enseignant = Enseignant::with('niveaux')->find($id);

    if (!$enseignant) {
        return response()->json(['message' => 'Enseignant non trouvé'], 404);
    }

    return response()->json($enseignant->niveaux);
}

public function teacherActivity() {
  return Enseignant::select('id', 'UPDATED_AT')->get();
}

public function getMatieres($id)
    {
        $enseignant = \App\Models\Enseignant::with('matieres')->find($id);

        if (!$enseignant) {
            return response()->json(['message' => 'Enseignant introuvable.'], 404);
        }

        return response()->json(['matieres' => $enseignant->matieres]);
    }
    
}
