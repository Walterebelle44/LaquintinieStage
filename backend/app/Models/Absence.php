<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_APPROUVEE = 'approuvee';
    const STATUT_REJETEE = 'rejetee';

    protected $fillable = [
        'stagiaire_id', 'date_debut', 'date_fin', 'motif', 'justificatif_path',
        'statut', 'traite_par_id', 'commentaire_traitement',
    ];

    protected function casts(): array
    {
        return ['date_debut' => 'date', 'date_fin' => 'date'];
    }

    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class);
    }

    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par_id');
    }
}
