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
         $filiere = Filiere::where('code', $row['filiere'])->first();
         $niveau = Niveau::where('code',$row['niveau'])->first();
    
   
        return new Matiere([
            'code' => $row['code'],
            'nom'  => $row['nom'],          
            'filiere_id'   => $filiere->id,  // conversion texte → id
            'niveau_id'    =>$niveau->id ,    // conversion texte → id

            ]);
       
    }
}
