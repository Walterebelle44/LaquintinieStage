<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    const TYPE_CONVENTION = 'convention';
    const TYPE_RAPPORT = 'rapport';
    const TYPE_MEMOIRE = 'memoire';
    const TYPE_ATTESTATION = 'attestation';
    const TYPE_LIVRABLE = 'livrable';
    const TYPE_AUTRE = 'autre';

    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_VALIDE = 'valide';
    const STATUT_REJETE = 'rejete';

    protected $fillable = [
        'stagiaire_id', 'type', 'titre', 'chemin_fichier', 'taille_fichier',
        'statut', 'valide_par_id', 'commentaire', 'depose_par_id',
    ];

    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class);
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par_id');
    }

    public function deposePar()
    {
        return $this->belongsTo(User::class, 'depose_par_id');
    }
}
