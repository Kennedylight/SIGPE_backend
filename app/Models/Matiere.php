<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'code',
        'filiere_id',
        'niveau_id',
    ];
    public function enseignants()
{
    return $this->belongsToMany(Enseignant::class);
}

}
