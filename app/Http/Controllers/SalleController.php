<?php

namespace App\Http\Controllers;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SalleImport;
use App\Models\Salle;

use Illuminate\Http\Request;

class SalleController extends Controller
{
    public function index()
    {
        return Salle::all();
    }

    public function ImportExcel (Request $request){
         $request->validate([
        'fichier' => 'required|file|mimes:xlsx,xls,csv'
    ]);

    Excel::import(new SalleImport, $request->file('fichier'));
    return response()->json(['message' => 'Importation réussie !'], 200);

    }
}
