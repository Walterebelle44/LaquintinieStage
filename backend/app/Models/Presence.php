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

    // Par défaut, Laravel sérialise un cast 'date' en JSON comme un datetime ISO complet
    // (ex: "2026-09-27T00:00:00.000000Z"), ce qui empêche le frontend de comparer p.date
    // à une simple chaîne "AAAA-MM-JJ" pour détecter le pointage du jour. On force donc
    // un format Y-m-d pur.
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class);
    }
}
