<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RendezVous;
use App\Models\Stagiaire;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RendezVousController extends Controller
{
    // Vue globale : réservée à l'admin (tous les rendez-vous de l'application)
    public function index(Request $request)
    {
        $query = RendezVous::query()->with(['stagiaire.user:id,nom,prenom', 'creePar:id,nom,prenom']);

        $user = $request->user();
        if ($user->isEncadrant()) {
            $query->whereHas('stagiaire', fn ($q) => $q->where('encadrant_id', $user->id));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }
        if ($request->filled('a_partir_de')) {
            $query->where('date_heure', '>=', $request->date('a_partir_de'));
        }

        return response()->json($query->orderBy('date_heure')->paginate($request->integer('per_page', 20)));
    }

    public function store(Request $request, Stagiaire $stagiaire)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:150',
            'description' => 'nullable|string',
            'date_heure' => 'required|date',
            'lieu' => 'nullable|string|max:150',
        ]);

        $rdv = $stagiaire->rendezVous()->create([...$data, 'cree_par_id' => $request->user()->id]);

        activity('rendez_vous')->causedBy($request->user())->performedOn($rdv)
            ->log("Rendez-vous planifié avec {$stagiaire->user->nom_complet}");

        return response()->json($rdv, 201);
    }

    public function update(Request $request, RendezVous $rendezVous)
    {
        $data = $request->validate([
            'titre' => 'sometimes|string|max:150',
            'description' => 'nullable|string',
            'date_heure' => 'sometimes|date',
            'lieu' => 'nullable|string|max:150',
            'statut' => ['sometimes', Rule::in(['planifie', 'termine', 'annule'])],
            'compte_rendu' => 'nullable|string',
        ]);

        $rendezVous->update($data);

        return response()->json($rendezVous);
    }

    public function destroy(RendezVous $rendezVous)
    {
        $rendezVous->delete();

        return response()->json(['message' => 'Rendez-vous supprimé.']);
    }
}
