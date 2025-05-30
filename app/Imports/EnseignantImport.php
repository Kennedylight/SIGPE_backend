<?php

namespace App\Imports;

use App\Models\Enseignant;
use Maatwebsite\Excel\Concerns\ToModel;

use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EnseignantImport implements ToModel , WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $enseignant = Enseignant::where('matricule' , $row['matricule'])->where('email' , $row['email'])->first();

        if(!$enseignant){
        return new Enseignant([
            'matricule' => $row['matricule'],
            'nom'  => $row['nom'],
            'prenom'   => $row['prenom'],
            'email'   => $row['email'],            
            
        ]);
        }
    }
}
