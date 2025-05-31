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

    protected $guard = 'enseignant-api';
    
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'matricule',
        'photo',
        'password',
        'device_token'
    ];

    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'enseignant_matiere', 'enseignant_id', 'matiere_id')
                    ->withTimestamps();
    }
    // public function matieres()
    // {
    //     return $this->belongsToMany(Matiere::class);
    // }

public function filieres()
{
    return $this->belongsToMany(Filiere::class, 'enseignant_filiere', 'enseignant_id', 'filiere_id');
}
public function niveaux()
{
    return $this->belongsToMany(Niveau::class);
}
public function sessions()
{
    return $this->belongsToMany(Session::class);
}
public function routeNotificationForFcm()
    {
        return $this->device_token; // ou fcm_token si tu renommes la colonne
    }

    public function getPhotoAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

}
