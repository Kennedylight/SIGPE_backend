<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

class Etudiant extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'filiere',
        'niveau',
        'matricule',
        'photo'
    ];
    public function session()
    {
        return $this->belongsToMany(Session::class);
    }
}
