<?php

namespace App\Imports;

use App\Models\Test;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Etudiant;
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
        // Cherche l'ID de la filière et du niveau selon les noms dans le fichier Excel
    $filiere = Filiere::where('code', $row['filiere'])->first();
    $niveau = Niveau::where('code', $row['niveau'])->first();
        return new Etudiant([
            'matricule' => $row['matricule'],
            'nom'  => $row['nom'],
            'prenom'   => $row['prenom'],
            'Date_nais'   => $row['date_naiss'],
            'sexe'   => $row['sexe'],
            'email'   => $row['email'],            
            'filiere'   => $filiere ? $filiere->id : null,  // conversion texte → id
            'niveau'    => $niveau ? $niveau->id : null,    // conversion texte → id

            ]);
    }
}
