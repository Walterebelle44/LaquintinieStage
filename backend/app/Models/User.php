<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Rôles disponibles :
 *  - admin      : accès total (comptes, encadrants, logs, rendez-vous, référentiels...)
 *  - encadrant  : gère ses stagiaires assignés (tuteur entreprise / encadrant)
 *  - stagiaire  : consulte sa feuille de route, pointe ses présences, dépose ses documents
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity;

    const ROLE_ADMIN = 'admin';
    const ROLE_ENCADRANT = 'encadrant';
    const ROLE_STAGIAIRE = 'stagiaire';

    protected $fillable = [
        'nom', 'prenom', 'email', 'telephone', 'password', 'role',
        'photo', 'is_active', 'is_blocked', 'blocked_reason',
        'derniere_connexion_at', 'created_by',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['nom_complet'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
            'derniere_connexion_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nom', 'prenom', 'email', 'role', 'is_active', 'is_blocked'])
            ->logOnlyDirty()
            ->useLogName('utilisateur');
    }

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    // Fiche stagiaire liée (si role = stagiaire)
    public function stagiaire()
    {
        return $this->hasOne(Stagiaire::class);
    }

    // Stagiaires encadrés (si role = encadrant)
    public function stagiairesEncadres()
    {
        return $this->hasMany(Stagiaire::class, 'encadrant_id');
    }

    public function rendezVousCrees()
    {
        return $this->hasMany(RendezVous::class, 'cree_par_id');
    }

    public function creePar()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEncadrant(): bool
    {
        return $this->role === self::ROLE_ENCADRANT;
    }

    public function isStagiaire(): bool
    {
        return $this->role === self::ROLE_STAGIAIRE;
    }

    public function peutSeConnecter(): bool
    {
        return $this->is_active && ! $this->is_blocked;
    }
}
