<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stagiaire;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EvaluationController extends Controller
{
    public function index(Stagiaire $stagiaire)
    {
        return response()->json($stagiaire->evaluations()->with('evaluateur:id,nom,prenom')->latest()->get());
    }

    // Notation par l'encadrant : grille académique, bilan de compétences ou suivi mensuel
    public function store(Request $request, Stagiaire $stagiaire)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['grille_academique', 'bilan_competences', 'suivi_mensuel'])],
            'periode' => 'nullable|string|max:100',
            'criteres' => 'nullable|array',
            'note_globale' => 'nullable|numeric|min:0|max:20',
            'appreciation' => 'nullable|string',
            'points_forts' => 'nullable|string',
            'axes_amelioration' => 'nullable|string',
            'date_evaluation' => 'required|date',
        ]);

        $evaluation = $stagiaire->evaluations()->create([
            ...$data,
            'evaluateur_id' => $request->user()->id,
        ]);

        activity('evaluation')->causedBy($request->user())->performedOn($evaluation)
            ->log("Nouvelle évaluation ({$data['type']}) pour {$stagiaire->user->nom_complet}");

        return response()->json($evaluation, 201);
    }

    public function update(Request $request, Stagiaire $stagiaire, \App\Models\Evaluation $evaluation)
    {
        $data = $request->validate([
            'criteres' => 'nullable|array',
            'note_globale' => 'nullable|numeric|min:0|max:20',
            'appreciation' => 'nullable|string',
            'points_forts' => 'nullable|string',
            'axes_amelioration' => 'nullable|string',
            'valide' => 'sometimes|boolean',
        ]);

        $evaluation->update($data);

        return response()->json($evaluation);
    }
}
