<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'etudiant_id',
        'statut',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }
    public function justificatif()
    {
        return $this->belongsTo(Justificatifs::class);
    }
}
