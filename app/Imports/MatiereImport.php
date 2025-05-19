<?php

namespace App\Imports;

use App\Models\Matiere;
use App\Models\Filere;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Etudiant;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class MatiereImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
         $filiere = Filiere::where('nom', $row['filiere'])->first();
    $niveau = Niveau::where('nom', $row['niveau'])->first();
   
        return new Matiere([
            'code' => $row['code'],
            'nom'  => $row['nom'],          
            'filiere_id'   => $filiere ? $filiere->id : null,  // conversion texte → id
            'niveau_id'    => $niveau ? $niveau->id : null,    // conversion texte → id

            ]);
       
    }
}
