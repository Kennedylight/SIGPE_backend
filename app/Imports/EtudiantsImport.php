<?php

namespace App\Imports;

use App\Models\Test;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EtudiantsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Test([
            'matricule' => $row['matricule'],
            'nom'  => $row['nom'],
            'prenom'   => $row['prenom'],
            'Date_nais'   => $row['date_naiss'],
            'sexe'   => $row['sexe'],
            'email'   => $row['email'],
            'filiere'   => $row['filiere'],
            'niveau'   => $row['niveau'],

            ]);
    }
}
