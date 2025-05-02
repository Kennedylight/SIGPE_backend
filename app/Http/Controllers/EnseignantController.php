<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
    //recuperer les sessions de tout les enseignants
    public function sessionsParEnseignant($enseignantId)
{
    $enseignant = Enseignant::with('sessions')->findOrFail($enseignantId);
    return response()->json($enseignant->sessions);
}

}
