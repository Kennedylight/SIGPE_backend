<?php

namespace App\Http\Controllers;

use App\Models\{Salle, Enseignant, Etudiant, Filiere, Niveau, Matiere};
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

    // CRUD Générique
    public function index($model)
    {
        $className = "App\\Models\\".ucfirst(Str::singular($model));
        if (!class_exists($className)) {
            return response()->json(['error' => 'Modèle non trouvé'], 404);
        }

        $data = $className::paginate(10);
        return response()->json($data);
    }

    public function store(Request $request, $model)
    {
        $className = "App\\Models\\".ucfirst(Str::singular($model));
        //$className = "App\\Models\\".ucfirst($model);
        if (!class_exists($className)) {
            return response()->json(['error' => 'Modèle non trouvé'], 404);
        }

        $data = $request->validate($this->getValidationRules($model));
        $item = $className::create($data);

        return response()->json($item, 201);
    }

    public function update(Request $request, $model, $id)
    { 
        $className = "App\\Models\\".ucfirst(Str::singular($model));
        //$className = "App\\Models\\".ucfirst($model);
        if (!class_exists($className)) {
            return response()->json(['error' => 'Modèle non trouvé'], 404);
        }

        $item = $className::findOrFail($id);
        $data = $request->validate($this->getValidationRules($model, $id));
        $item->update($data);

        return response()->json($item);
    }

    public function destroy($model, $id)
    {
        $className = "App\\Models\\".ucfirst(Str::singular($model));
        //$className = "App\\Models\\".ucfirst($model);
        if (!class_exists($className)) {
            return response()->json(['error' => 'Modèle non trouvé'], 404);
        }

        $item = $className::findOrFail($id);
        $item->delete();

        return response()->json(null, 204);
    }

    // Gestion des relations Enseignant
    public function linkTeacherToEntities(Request $request, $teacherId)
    {
        $request->validate([
            'filieres' => 'nullable|array',
            'filieres.*' => 'exists:filieres,id',
            'niveaux' => 'nullable|array',
            'niveaux.*' => 'exists:niveaux,id',
            'matieres' => 'nullable|array',
            'matieres.*' => 'exists:matieres,id'
        ]);

        $teacher = Enseignant::findOrFail($teacherId);

        if ($request->filieres) {
            $teacher->filieres()->sync($request->filieres);
        }

        if ($request->niveaux) {
            $teacher->niveaux()->sync($request->niveaux);
        }

        if ($request->matieres) {
            $teacher->matieres()->sync($request->matieres);
        }

        return response()->json([
            'message' => 'Associations mises à jour',
            'teacher' => $teacher->load('filieres', 'niveaux', 'matieres')
        ]);
    }

    private function getValidationRules($model, $id = null)
    {
        $model = Str::singular($model);
        
        $rules = [
            'salle' => [
                'nom' => 'required|string|max:255',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'rayon_metres' => 'required|integer'
            ],
            'etudiant' => [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:etudiants,email,'.$id,
                'matricule' => 'required|string|unique:etudiants,matricule,'.$id,
                'filiere_id' => 'required|exists:filieres,id',
                'niveau_id' => 'required|exists:niveaux,id',
                'password' => 'sometimes|string|min:8'
            ],
            'enseignant' => [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:enseignants,email,'.$id,
                'matricule' => 'required|string|unique:enseignants,matricule,'.$id,
                'password' => 'sometimes|string|min:8'
            ],
            'filiere' => [
                'nom' => 'required|string|max:255|unique:filieres,nom,'.$id,
                'code' => 'required|string|max:50|unique:filieres,code,'.$id
            ],
            'niveau' => [
                'nom' => 'required|string|max:255|unique:niveaux,nom,'.$id,
                'code' => 'required|string|max:50|unique:niveaux,code,'.$id
            ],
            'matiere' => [
                'nom' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:matieres,code,'.$id,
                'filiere_id' => 'required|exists:filieres,id',
                'niveau_id' => 'required|exists:niveaux,id'
            ]
        ];

        return $rules[$model] ?? [];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request){
        $validator  = Validator::make($request->all(), [
            "email" => "required|string||max:255",
            "password" => "required|string|max:160",
        ]);
        if ($validator->fails()) {
            return response(["errors" => $validator->errors()->all()], 422);
        }
        $admin = Admin::where("email", $request->email)->first();
        if($admin){
            if(Hash::check($request->password , $admin->password)){
                $token = $admin->createToken("Laravel Password Grant Client")->accessToken;
                $response = ["token" => $token];
                return response(['access_token' => $token , "admin"=>$admin]);
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

}
