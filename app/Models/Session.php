<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    protected $fillable = [
        'statut',
        'heure_debut',
        'heure_fin',
        'utilisateur_id',
        'lien',
        'description',
        'salle_id',
    ];
    public function etudiants()
    {
        return $this->belongsToMany(Etudiant::class);
    }
    public function salle()
    {
        return $this->hasMany(Salle::class);
    }
}
