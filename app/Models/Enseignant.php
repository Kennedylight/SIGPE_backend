<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Enseignant extends Authenticatable
{
    use HasFactory , HasApiTokens, Notifiable;
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'matricule',
        'photo'
    ];
    public function matieres()
{
    return $this->belongsToMany(Matiere::class);
}
}
