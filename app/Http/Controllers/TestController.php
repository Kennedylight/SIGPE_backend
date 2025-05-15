<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EtudiantsImport;

class TestController extends Controller
{
    public function importerExcel(Request $request)
{
    $request->validate([
        'fichier' => 'required|file|mimes:xlsx,xls,csv'
    ]);

    Excel::import(new EtudiantsImport, $request->file('fichier'));

    return back()->with('success', 'Importation réussie !');
}
}
