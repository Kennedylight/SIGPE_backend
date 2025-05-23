<?php

namespace App\Imports;
use App\Models\Niveau;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NiveauImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $niveau = Niveau::where('code' , $row['code'])->first();

        if(!$niveau){
        return new Niveau([
              'code' => $row['code'],
              'nom'  => $row['nom'],    
        ]);
    }
    }
}
