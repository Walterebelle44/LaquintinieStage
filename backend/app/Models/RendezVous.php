<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    const STATUT_PLANIFIE = 'planifie';
    const STATUT_TERMINE = 'termine';
    const STATUT_ANNULE = 'annule';

    protected $fillable = [
        'stagiaire_id', 'cree_par_id', 'titre', 'description',
        'date_heure', 'lieu', 'statut', 'compte_rendu',
    ];

    protected function casts(): array
    {
        return ['date_heure' => 'datetime'];
    }

    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class);
    }

    public function creePar()
    {
        return $this->belongsTo(User::class, 'cree_par_id');
    }
}
