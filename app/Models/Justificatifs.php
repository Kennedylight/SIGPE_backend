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

    ];
    public function enseignants()
    {
        return $this->belongsToMany(Enseignant::class);
    }
 
    public function etudiant()
{
    return $this->belongsTo(Etudiant::class, 'etudiant_id');
}
}
