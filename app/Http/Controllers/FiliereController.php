<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Filere;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FiliereImport;

class FiliereController extends Controller
{
    public function index()
    {
        return Filere::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:100',
        ]);

        $Filere = Filere::create($validated);
        return response()->json($Filere, 201);
    }

    public function show($id)
    {
        return Filere::findOrFail($id);
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
        $Filere = Filere::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:100',
        ]);

        $Filere->update($validated);
        return response()->json($Filere);
    }
    
    public function destroy($id)
    {
        $Filere = Filere::findOrFail($id);
        $Filere->delete();

        return response()->json(null, 204);
    }
}
