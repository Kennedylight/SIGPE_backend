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
        'filere_id',
        'niveau_id',
        'salle_id',
    ];
    public function etudiants()
    {
        return $this->belongsToMany(Etudiant::class);
    }
    public function enseignants()
    {
        return $this->belongsToMany(Enseignant::class);
    }
    public function filieres()
    {
        return $this->belongsToMany(Filere::class);
    }
    public function niveaux()
    {
        return $this->belongsToMany(Niveau::class);
    }
    public function salle()
    {
        return $this->hasMany(Salle::class);
    }
}
