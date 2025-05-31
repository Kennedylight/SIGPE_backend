<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justificatifs extends Model
{
    use HasFactory;
    protected $fillable = [
        'message',
        'etudiant_id',     
        'enseignant_id',     
        'piece_jointes',     
        'matiere_id',         
        'reponse_enseignant',   
        'presence_id', 
        'reponse_enseignant',
        'statut'
    ];
    // public function enseignants()
    // {
    //     return $this->belongsToMany(Enseignant::class);
    // }
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'enseignant_id');
    }
    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }
    public function presence()
    {
        return $this->belongsTo(Presence::class, 'presence_id');
    }
    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }
}
