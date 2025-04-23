<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'filiere',
        'niveau',
    ];
    public function enseignants()
{
    return $this->belongsToMany(Enseignant::class);
}

}
