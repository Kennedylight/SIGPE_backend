<?php

namespace App\Imports;

use App\Models\Salle;
use App\Models\Matiere;
use App\Models\Filere;
use App\Models\Niveau;
use App\Models\Etudiant;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Maatwebsite\Excel\Concerns\ToModel;

class SalleImport implements ToModel , WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $salle = Salle::where('nom' , $row['nom'])->first();

        if(!$salle){
            return new Salle([
                'nom' => $row['nom'],
                'latitude'  => $row['latitude'],
                'longitude'  => $row['longitude'],
                'rayon_metres'  => $row['rayon_metres'],      
            ]);
        } 
    }
}
