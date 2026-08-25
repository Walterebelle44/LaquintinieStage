<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Stagiaire extends Model
{
    use HasFactory, LogsActivity;

    const TYPE_ACADEMIQUE = 'academique';
    const TYPE_PROFESSIONNEL = 'professionnel';

    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_EN_COURS = 'en_cours';
    const STATUT_TERMINE = 'termine';
    const STATUT_ABANDONNE = 'abandonne';

    protected $fillable = [
        'user_id', 'matricule', 'type_stage', 'encadrant_id', 'etablissement_id',
        'maitre_stage_ecole_nom', 'maitre_stage_ecole_email', 'maitre_stage_ecole_telephone',
        'service', 'sujet', 'date_debut', 'date_fin', 'statut',
        'convention_signee', 'referentiel_competences', 'niveau_etude',
        'employabilite_souhaitee', 'notes_generales',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'convention_signee' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['statut', 'encadrant_id', 'type_stage'])
            ->logOnlyDirty()
            ->useLogName('stagiaire');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function encadrant()
    {
        return $this->belongsTo(User::class, 'encadrant_id');
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class);
    }

    public function estAcademique(): bool
    {
        return $this->type_stage === self::TYPE_ACADEMIQUE;
    }

    // Progression de la feuille de route (basée sur les évaluations validées vs durée du stage)
    public function getProgressionAttribute(): int
    {
        if (! $this->date_debut || ! $this->date_fin) {
            return 0;
        }
        $total = $this->date_debut->diffInDays($this->date_fin) ?: 1;
        $ecoule = min($this->date_debut->diffInDays(now()), $total);

        return (int) round(($ecoule / $total) * 100);
    }
}
