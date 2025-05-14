<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'latitude',
        'longitude',
        'rayon_metres',
       
    ];
    public function session()
{
    return $this->hasMany(User::class);
}
}
