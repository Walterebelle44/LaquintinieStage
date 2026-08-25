<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stagiaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StagiaireController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Stagiaire::query()->with(['user:id,nom,prenom,email,photo', 'encadrant:id,nom,prenom', 'etablissement']);

        // Un encadrant ne voit que ses propres stagiaires ; l'admin voit tout
        if ($user->isEncadrant()) {
            $query->where('encadrant_id', $user->id);
        }

        if ($request->filled('type_stage')) {
            $query->where('type_stage', $request->string('type_stage'));
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }
        if ($request->filled('encadrant_id') && $user->isAdmin()) {
            $query->where('encadrant_id', $request->integer('encadrant_id'));
        }
        if ($request->filled('recherche')) {
            $terme = $request->string('recherche');
            $query->whereHas('user', fn ($q) => $q->where('nom', 'like', "%{$terme}%")
                ->orWhere('prenom', 'like', "%{$terme}%")
                ->orWhere('email', 'like', "%{$terme}%"));
        }

        return response()->json($query->latest()->paginate($request->integer('per_page', 15)));
    }

    public function show(Request $request, Stagiaire $stagiaire)
    {
        $this->autoriserAcces($request, $stagiaire);

        return response()->json($stagiaire->load([
            'user', 'encadrant', 'etablissement', 'presences' => fn ($q) => $q->latest('date')->limit(30),
            'absences' => fn ($q) => $q->latest(),
            'evaluations' => fn ($q) => $q->latest(),
            'documents' => fn ($q) => $q->latest(),
            'rendezVous' => fn ($q) => $q->orderByDesc('date_heure'),
        ]));
    }

    // Création complète : compte utilisateur + fiche stagiaire (admin ou encadrant)
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:8',
            'type_stage' => ['required', Rule::in(['academique', 'professionnel'])],
            'encadrant_id' => 'nullable|exists:users,id',
            'etablissement_id' => 'nullable|exists:etablissements,id',
            'maitre_stage_ecole_nom' => 'nullable|string|max:150',
            'maitre_stage_ecole_email' => 'nullable|email',
            'maitre_stage_ecole_telephone' => 'nullable|string|max:30',
            'niveau_etude' => 'nullable|string|max:100',
            'referentiel_competences' => 'nullable|string',
            'employabilite_souhaitee' => 'nullable|string|max:150',
            'service' => 'nullable|string|max:150',
            'sujet' => 'nullable|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
        ]);

        $stagiaire = DB::transaction(function () use ($data, $request) {
            $motDePasse = $data['password'] ?? Str::random(10);

            $user = User::create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'telephone' => $data['telephone'] ?? null,
                'password' => Hash::make($motDePasse),
                'role' => User::ROLE_STAGIAIRE,
                'created_by' => $request->user()->id,
            ]);

            $stagiaire = Stagiaire::create([
                'user_id' => $user->id,
                'matricule' => 'STG-'.now()->format('y').'-'.str_pad((Stagiaire::max('id') + 1), 4, '0', STR_PAD_LEFT),
                'type_stage' => $data['type_stage'],
                'encadrant_id' => $data['encadrant_id'] ?? ($request->user()->isEncadrant() ? $request->user()->id : null),
                'etablissement_id' => $data['etablissement_id'] ?? null,
                'maitre_stage_ecole_nom' => $data['maitre_stage_ecole_nom'] ?? null,
                'maitre_stage_ecole_email' => $data['maitre_stage_ecole_email'] ?? null,
                'maitre_stage_ecole_telephone' => $data['maitre_stage_ecole_telephone'] ?? null,
                'niveau_etude' => $data['niveau_etude'] ?? null,
                'referentiel_competences' => $data['referentiel_competences'] ?? null,
                'employabilite_souhaitee' => $data['employabilite_souhaitee'] ?? null,
                'service' => $data['service'] ?? null,
                'sujet' => $data['sujet'] ?? null,
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'statut' => Stagiaire::STATUT_EN_ATTENTE,
            ]);

            activity('stagiaire')
                ->causedBy($request->user())
                ->performedOn($stagiaire)
                ->log("Création du dossier stagiaire : {$user->nom_complet}");

            return $stagiaire;
        });

        return response()->json($stagiaire->load(['user', 'encadrant', 'etablissement']), 201);
    }

    public function update(Request $request, Stagiaire $stagiaire)
    {
        $this->autoriserAcces($request, $stagiaire);

        $data = $request->validate([
            'type_stage' => ['sometimes', Rule::in(['academique', 'professionnel'])],
            'encadrant_id' => 'nullable|exists:users,id',
            'etablissement_id' => 'nullable|exists:etablissements,id',
            'maitre_stage_ecole_nom' => 'nullable|string|max:150',
            'maitre_stage_ecole_email' => 'nullable|email',
            'maitre_stage_ecole_telephone' => 'nullable|string|max:30',
            'convention_signee' => 'sometimes|boolean',
            'niveau_etude' => 'nullable|string|max:100',
            'referentiel_competences' => 'nullable|string',
            'employabilite_souhaitee' => 'nullable|string|max:150',
            'service' => 'nullable|string|max:150',
            'sujet' => 'nullable|string|max:255',
            'date_debut' => 'sometimes|date',
            'date_fin' => 'sometimes|date|after:date_debut',
            'statut' => ['sometimes', Rule::in(['en_attente', 'en_cours', 'termine', 'abandonne'])],
            'notes_generales' => 'nullable|string',
        ]);

        $stagiaire->update($data);

        activity('stagiaire')
            ->causedBy($request->user())
            ->performedOn($stagiaire)
            ->log('Mise à jour du dossier stagiaire');

        return response()->json($stagiaire->fresh(['user', 'encadrant', 'etablissement']));
    }

    public function destroy(Request $request, Stagiaire $stagiaire)
    {
        activity('stagiaire')
            ->causedBy($request->user())
            ->log("Suppression du dossier stagiaire : {$stagiaire->user->nom_complet}");

        $stagiaire->user()->delete(); // cascade sur la fiche stagiaire

        return response()->json(['message' => 'Dossier stagiaire supprimé.']);
    }

    // Assigner / réassigner un encadrant (admin uniquement, contrôlé par la route)
    public function assignerEncadrant(Request $request, Stagiaire $stagiaire)
    {
        $data = $request->validate(['encadrant_id' => 'required|exists:users,id']);
        $stagiaire->update(['encadrant_id' => $data['encadrant_id']]);

        activity('stagiaire')
            ->causedBy($request->user())
            ->performedOn($stagiaire)
            ->log('Changement d\'encadrant');

        return response()->json($stagiaire->fresh('encadrant'));
    }

    private function autoriserAcces(Request $request, Stagiaire $stagiaire): void
    {
        $user = $request->user();
        abort_if(
            $user->isEncadrant() && $stagiaire->encadrant_id !== $user->id,
            403,
            "Vous n'encadrez pas ce stagiaire."
        );
    }
}
