<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory , HasApiTokens, Notifiable;

    protected $guard = 'admin-api';
    
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'photo',
        'password'
    ];

    public function getPhotoAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
}
