<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filere extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'code',     

    ];
    public function enseignants()
{
    return $this->belongsToMany(Enseignant::class);
}
public function sessions()
{
    return $this->belongsToMany(Session::class);
}

}
