<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Stagiaire;
use Illuminate\Http\Request;

/**
 * Restreint l'accès d'un encadrant aux seuls stagiaires qu'il encadre.
 * L'admin n'est jamais concerné par cette restriction (accès total).
 */
trait VerifieAccesStagiaire
{
    protected function verifierAccesStagiaire(Request $request, Stagiaire $stagiaire): void
    {
        $user = $request->user();

        abort_if(
            $user->isEncadrant() && $stagiaire->encadrant_id !== $user->id,
            403,
            "Vous n'encadrez pas ce stagiaire."
        );
    }
}