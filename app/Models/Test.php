<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;
      protected $fillable = ['matricule','nom', 'prenom', 'email','Date_nais','sexe','filiere','niveau'];
}
