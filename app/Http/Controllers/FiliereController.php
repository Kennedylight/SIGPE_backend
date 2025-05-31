<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
// use App\Models\Filere;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FiliereImport;

class FiliereController extends Controller
{
    public function index()
    {
        return Filiere::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:100',
        ]);

        $Filiere = Filiere::create($validated);
        return response()->json($Filiere, 201);
    }

    public function show($id)
    {
        return Filiere::findOrFail($id);
    }

     public function ImportExcel(Request $request ){
        $request->validate([
        'fichier' => 'required|file|mimes:xlsx,xls,csv'
    ]);

    Excel::import(new FiliereImport, $request->file('fichier'));
    return response()->json(['message' => 'Importation réussie !'], 200);

    }

    public function update(Request $request, $id)
    {
        $Filiere = Filiere::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:100',
        ]);

        $Filiere->update($validated);
        return response()->json($Filiere);
    }
    
    public function destroy($id)
    {
        $Filiere = Filiere::findOrFail($id);
        $Filiere->delete();

        return response()->json(null, 204);
    }
}
