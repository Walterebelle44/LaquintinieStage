<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Grille académique OU bilan de compétences (parcours professionnel), notée par l'encadrant
class Evaluation extends Model
{
    const TYPE_GRILLE_ACADEMIQUE = 'grille_academique';
    const TYPE_BILAN_COMPETENCES = 'bilan_competences';
    const TYPE_SUIVI_MENSUEL = 'suivi_mensuel';

    protected $fillable = [
        'stagiaire_id', 'evaluateur_id', 'type', 'periode', 'criteres',
        'note_globale', 'appreciation', 'points_forts', 'axes_amelioration',
        'valide', 'date_evaluation',
    ];

    protected function casts(): array
    {
        return [
            'criteres' => 'array',
            'valide' => 'boolean',
            'date_evaluation' => 'date',
            'note_globale' => 'decimal:2',
        ];
    }

    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class);
    }

    public function evaluateur()
    {
        return $this->belongsTo(User::class, 'evaluateur_id');
    }
}
