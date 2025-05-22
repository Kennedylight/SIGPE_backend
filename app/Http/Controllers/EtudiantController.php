<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EtudiantsImport;

class EtudiantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request){
        $validator  = Validator::make($request->all(), [
            "matricule" => "required|string||max:255",
            "password" => "required|string|max:160",
        ]);
        if ($validator->fails()) {
            return response(["errors" => $validator->errors()->all()], 422);
        }
        $etudiant = Etudiant::where("matricule", $request->matricule)->first();
        if($etudiant){
            if(Hash::check($request->password , $etudiant->password)){
                $token = $etudiant->createToken("Laravel Password Grant Client")->accessToken;
                $response = ["token" => $token];
                return response(['access_token' => $token , "etudiant"=>$etudiant]);
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
    
//creer un etudiants
    public function store(Request $request)
    {

        $validator  = Validator::make($request->all(),[
            "nom"=>"required|string|max:160",
            "email"=>"required|string|email|max:255|unique:etudiants",
            "matricule"=>"required|string||max:255|unique:etudiants",
            "password"=>"required|string|max:160",
        ]);
        if($validator->fails()){
            return response(["errors"=>$validator->errors()->all()],422);
        }
        $request['password'] = Hash::make($request["password"]);
        $request["remember_token"] = Str::random(10);
        $etudiant = Etudiant::create($request->toArray());
        $token = $etudiant->createToken("Laravel Password Grant Client")->accessToken;

        return response()->json(["access_token" =>$token , "etudiant" =>$etudiant]);
    }

    public function ImportExcel(Request $request)
    {
    $request->validate([
        'fichier' => 'required|file|mimes:xlsx,xls,csv'
    ]);

    Excel::import(new EtudiantsImport, $request->file('fichier'));
    return response()->json(['message' => 'Importation réussie !'], 200);

    }
     // Notifications (lues + non lues)
public function mesNotifications()
{
   
    return auth()->user()->notifications;
}

// Ou juste les non lues :
public function mesNotificationsNonLues()
{
    return auth()->user()->unreadNotifications;
}

# Ajouté par le dev du FRONT END ------------------------------------------------------------------------------
    public function updateDeviceToken(Request $request, $id) {
    $etudiant = Etudiant::findOrFail($id);
    $etudiant->device_token = $request->input('device_token');
    $etudiant->save();

    return response()->json(['message' => 'Token mis à jour']);
}



    
    public function index()
    {
        //
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
