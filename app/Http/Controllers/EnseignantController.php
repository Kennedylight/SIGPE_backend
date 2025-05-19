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




}
