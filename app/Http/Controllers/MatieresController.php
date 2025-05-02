<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Matiere;
use Illuminate\Http\Request;

class MatieresController extends Controller
{
    public function index()
    {
        $matieres =  Matiere::all();
        return response()->json(['matieres'=>$matieres]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'filiere' => 'required|string|max:255',
            'niveau' => 'required|string|max:255',
        ]);

        $matiere = Matiere::create($validated);

        return response()->json(['matiere'=>$matiere], 201);
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

        return response()->json(['matiere'=>$matiere]);
    }

    public function destroy($id)
    {
        $matiere = Matiere::findOrFail($id);
        $matiere->delete();

        return response()->json(null, 204);
    }
}
