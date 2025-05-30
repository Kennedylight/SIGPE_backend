<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Etudiant extends Authenticatable
{
    use HasFactory , HasApiTokens, Notifiable;

    protected $guard = 'etudiant-api';
    
        protected $fillable = [
        'nom',
        'prenom',
        'email',
        'filiere_id',
        'niveau_id',
        'matricule',
        'photo',
        'password',
        'Date_nais',
        'sexe',
        'device_token'
    ];
    public function session()
    {
        return $this->belongsToMany(Session::class);
    }
    public function filiere(){
        return $this->belongsTo(Filiere::class);
    }
    public function niveau(){
        return $this->belongsTo(Niveau::class);
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
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
