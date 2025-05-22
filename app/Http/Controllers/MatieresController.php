<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enseignant;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MatiereImport;

class MatieresController extends Controller
{
    public function getMatieresByEnseignant($id)
    {
        $enseignant = Enseignant::with('matieres')->find($id);
    
        if (!$enseignant) {
            return response()->json(['message' => 'Enseignant non trouvé.'], 404);
        }
    
        if ($enseignant->matieres->isEmpty()) {
            return response()->json(['message' => 'Aucune matière pour cet enseignant.'], 404);
        }
    
        return response()->json([
            'enseignant' => $enseignant->nom,
            'matieres' => $enseignant->matieres,
        ]);
    }
    
    public function getMatieresByEnseignantFiliereAndNiveau(Request $request, $id)
{
    $request->validate([
        'filiere' => 'required|integer|exists:filieres,id',
        'niveau' => 'required|integer|exists:niveaux,id',
    ]);

    $enseignant = Enseignant::find($id);

    if (!$enseignant) {
        return response()->json(['message' => 'Enseignant non trouvé.'], 404);
    }

    // Filtrer les matières de cet enseignant selon la filière et le niveau
    $matieres = $enseignant->matieres()
        ->where('filiere_id', $request->filiere)
        ->where('niveau_id', $request->niveau)
        ->get();

    return response()->json([
        'enseignant' => $enseignant->nom,
        'filiere' => $request->filiere,
        'niveau' => $request->niveau,
        'matieres' => $matieres,
    ]);
}
    public function index()
    {
        $matieres =  Matiere::all();
        return response()->json(['matieres' => $matieres]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'filiere' => 'required|string|max:255',
            'niveau' => 'required|string|max:255',
        ]);

        $matiere = Matiere::create($validated);

        return response()->json(['matiere' => $matiere], 200);
    }

    public function ImportExcel(Request $request){
        $request->validate([
        'fichier' => 'required|file|mimes:xlsx,xls,csv'
    ]);

    Excel::import(new MatiereImport, $request->file('fichier'));
    return response()->json(['message' => 'Importation réussie !'], 200);
    }
    public function show($id)
    {
        return Matiere::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $matiere = Matiere::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'filiere' => 'sometimes|string|max:255',
            'niveau' => 'sometimes|string|max:255',
        ]);

        $matiere->update($validated);

        return response()->json(['matiere' => $matiere]);
    }

    public function destroy($id)
    {
        $matiere = Matiere::findOrFail($id);
        $matiere->delete();

        return response()->json(null, 204);
    }

    // Ajoutés par le dev du FRONT END -------------------------------------------------------------------------
    public function getMatieresByFiliereAndNiveau(Request $request)
{
    $request->validate([
        'filiere' => 'required|integer|exists:filieres,id',
        'niveau' => 'required|integer|exists:niveaux,id',
    ]);

    $filiereId = $request->filiere;
    $niveauId = $request->niveau;

    // Récupérer les matières liées à la filière et au niveau spécifiés
    $matieres = Matiere::where('filiere_id', $filiereId)
        ->where('niveau_id', $niveauId)
        ->get();

    return response()->json([
        'filiere' => $filiereId,
        'niveau' => $niveauId,
        'matieres' => $matieres,
    ]);
}

}
