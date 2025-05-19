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
        'matiere_id',
        'filiere_id',
        'niveau_id',
        'salle_id',
        'enseignant_id'
    ];
    public function etudiants()
    {
        return $this->belongsToMany(Etudiant::class);
    }
   public function enseignant()
{
    return $this->belongsTo(Enseignant::class, 'enseignant_id');
}

    // public function enseignants()
    // {
    //     return $this->belongsToMany(Enseignant::class);
    // }
    // public function filieres()
    // {
    //     return $this->belongsToMany(Filere::class);
    // }
    // public function niveaux()
    // {
    //     return $this->belongsToMany(Niveau::class);
    // }
    // public function salle()
    // {
    //     return $this->hasMany(Salle::class);
    // }

    // Ajouté par le dev du FRONT END ----------------------------------------------------------
    public function salle()
{
    return $this->belongsTo(Salle::class);
}

    public function matiere() {
        return $this->belongsTo(Matiere::class);
    }

public function filiere()
{
    return $this->belongsTo(Filiere::class, 'filiere_id');
}

public function niveau()
{
    return $this->belongsTo(Niveau::class, 'niveau_id');
}

}
