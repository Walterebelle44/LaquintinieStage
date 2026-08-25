<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Espace personnel du stagiaire connecté : feuille de route, présences, documents, rendez-vous
class MonEspaceController extends Controller
{
    public function feuilleDeRoute(Request $request)
    {
        $stagiaire = $request->user()->stagiaire;
        abort_unless($stagiaire, 404, "Aucune fiche stagiaire associée à ce compte.");

        return response()->json($stagiaire->load([
            'encadrant:id,nom,prenom,email,telephone',
            'etablissement',
            'presences' => fn ($q) => $q->latest('date')->limit(30),
            'absences' => fn ($q) => $q->latest(),
            'evaluations' => fn ($q) => $q->where('valide', true)->latest(),
            'documents' => fn ($q) => $q->latest(),
            'rendezVous' => fn ($q) => $q->where('date_heure', '>=', now()->subDays(1))->orderBy('date_heure'),
        ]));
    }
}
