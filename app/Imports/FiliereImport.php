<?php

namespace App\Imports;

use App\Models\Filere;
use App\Models\Matiere;
use App\Models\Niveau;
use App\Models\Etudiant;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class FiliereImport implements ToModel , WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Filere([
    
              'code' => $row['code'],
              'nom'  => $row['nom'],    
        ]);

    }
}
