<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Établissement partenaire (école/université) pour les stages académiques
class Etablissement extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'ville', 'filiere', 'contact_nom', 'contact_email', 'contact_telephone',
    ];

    public function stagiaires()
    {
        return $this->hasMany(Stagiaire::class);
    }
}
