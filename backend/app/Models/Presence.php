<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    const STATUT_PRESENT = 'present';
    const STATUT_RETARD = 'retard';
    const STATUT_ABSENT = 'absent';

    protected $fillable = [
        'stagiaire_id', 'date', 'heure_arrivee', 'heure_depart', 'statut', 'commentaire',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class);
    }
}
