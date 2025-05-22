<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Niveau;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\NiveauImport;

class NiveauController extends Controller
{
    public function index()
    {
        return Niveau::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:100',
        ]);

        $Niveau = Niveau::create($validated);
        return response()->json($Niveau, 201);
    }
    public function ImportExcel(Request $request ){
        $request->validate([
        'fichier' => 'required|file|mimes:xlsx,xls,csv'
    ]);

    Excel::import(new NiveauImport, $request->file('fichier'));
    return response()->json(['message' => 'Importation réussie !'], 200);

    }
    public function show($id)
    {
        return Niveau::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $Niveau = Niveau::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:100',
        ]);

        $Niveau->update($validated);
        return response()->json($Niveau);
    }

    public function destroy($id)
    {
        $Niveau = Niveau::findOrFail($id);
        $Niveau->delete();

        return response()->json("Suppression reussi", 204);
    }
}
