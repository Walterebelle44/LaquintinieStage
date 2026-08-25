<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Stagiaire;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AbsenceController extends Controller
{
    public function index(Request $request, Stagiaire $stagiaire)
    {
        return response()->json($stagiaire->absences()->latest()->get());
    }

    // Le stagiaire dépose une demande d'absence
    public function store(Request $request)
    {
        $stagiaire = $request->user()->stagiaire;
        abort_unless($stagiaire, 404, "Aucune fiche stagiaire associée à ce compte.");

        $data = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'required|string|max:255',
        ]);

        $absence = $stagiaire->absences()->create([...$data, 'statut' => Absence::STATUT_EN_ATTENTE]);

        activity('absence')->causedBy($request->user())->performedOn($absence)
            ->log("Demande d'absence déposée");

        return response()->json($absence, 201);
    }

    // Approbation / rejet par l'encadrant ou l'admin
    public function traiter(Request $request, Absence $absence)
    {
        $data = $request->validate([
            'statut' => ['required', Rule::in([Absence::STATUT_APPROUVEE, Absence::STATUT_REJETEE])],
            'commentaire_traitement' => 'nullable|string|max:255',
        ]);

        $absence->update([...$data, 'traite_par_id' => $request->user()->id]);

        activity('absence')->causedBy($request->user())->performedOn($absence)
            ->log('Absence '.($data['statut'] === 'approuvee' ? 'approuvée' : 'rejetée'));

        return response()->json($absence);
    }
}
