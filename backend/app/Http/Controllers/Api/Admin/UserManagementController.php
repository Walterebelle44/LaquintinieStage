<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Réservé au rôle admin : gestion totale des comptes admin/encadrant,
 * consultation de tous les utilisateurs de l'application (y compris stagiaires).
 */
class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('creePar:id,nom,prenom');

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }
        if ($request->filled('recherche')) {
            $terme = $request->string('recherche');
            $query->where(function ($q) use ($terme) {
                $q->where('nom', 'like', "%{$terme}%")
                    ->orWhere('prenom', 'like', "%{$terme}%")
                    ->orWhere('email', 'like', "%{$terme}%");
            });
        }
        if ($request->filled('statut')) {
            match ($request->string('statut')->value()) {
                'actif' => $query->where('is_active', true)->where('is_blocked', false),
                'bloque' => $query->where('is_blocked', true),
                'inactif' => $query->where('is_active', false),
                default => null,
            };
        }

        return response()->json(
            $query->orderByDesc('created_at')->paginate($request->integer('per_page', 15))
        );
    }

    public function show(User $user)
    {
        return response()->json($user->load(['stagiaire', 'stagiairesEncadres']));
    }

    // Création d'un compte admin ou encadrant (les stagiaires sont créés via StagiaireController)
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:30',
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_ENCADRANT])],
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'created_by' => $request->user()->id,
        ]);

        activity('utilisateur')
            ->causedBy($request->user())
            ->performedOn($user)
            ->log("Création du compte {$user->role} : {$user->nom_complet}");

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'telephone' => 'nullable|string|max:30',
            'role' => ['sometimes', Rule::in([User::ROLE_ADMIN, User::ROLE_ENCADRANT, User::ROLE_STAGIAIRE])],
        ]);

        $user->update($data);

        activity('utilisateur')
            ->causedBy($request->user())
            ->performedOn($user)
            ->log("Modification du compte : {$user->nom_complet}");

        return response()->json($user);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 422);
        }

        activity('utilisateur')
            ->causedBy($request->user())
            ->log("Suppression du compte : {$user->nom_complet} ({$user->email})");

        $user->delete();

        return response()->json(['message' => 'Compte supprimé.']);
    }

    // Bloquer un compte (admin ou encadrant) avec motif
    public function bloquer(Request $request, User $user)
    {
        $data = $request->validate(['motif' => 'required|string|max:255']);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous bloquer vous-même.'], 422);
        }

        $user->update(['is_blocked' => true, 'blocked_reason' => $data['motif']]);

        activity('utilisateur')
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties(['motif' => $data['motif']])
            ->log("Blocage du compte : {$user->nom_complet}");

        return response()->json($user);
    }

    public function debloquer(Request $request, User $user)
    {
        $user->update(['is_blocked' => false, 'blocked_reason' => null]);

        activity('utilisateur')
            ->causedBy($request->user())
            ->performedOn($user)
            ->log("Déblocage du compte : {$user->nom_complet}");

        return response()->json($user);
    }

    // Restreindre = désactiver temporairement (différent du blocage disciplinaire)
    public function restreindre(Request $request, User $user)
    {
        $user->update(['is_active' => false]);

        activity('utilisateur')
            ->causedBy($request->user())
            ->performedOn($user)
            ->log("Restriction d'accès du compte : {$user->nom_complet}");

        return response()->json($user);
    }

    public function reactiver(Request $request, User $user)
    {
        $user->update(['is_active' => true]);

        activity('utilisateur')
            ->causedBy($request->user())
            ->performedOn($user)
            ->log("Réactivation du compte : {$user->nom_complet}");

        return response()->json($user);
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate(['password' => 'required|string|min:8']);
        $user->update(['password' => Hash::make($data['password'])]);

        activity('utilisateur')
            ->causedBy($request->user())
            ->performedOn($user)
            ->log("Réinitialisation du mot de passe de : {$user->nom_complet}");

        return response()->json(['message' => 'Mot de passe réinitialisé.']);
    }

    // Tableau de bord global admin : statistiques vue d'ensemble
    public function dashboard()
    {
        return response()->json([
            'total_utilisateurs' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_encadrants' => User::where('role', 'encadrant')->count(),
            'total_stagiaires' => User::where('role', 'stagiaire')->count(),
            'comptes_bloques' => User::where('is_blocked', true)->count(),
            'stagiaires_academiques' => \App\Models\Stagiaire::where('type_stage', 'academique')->count(),
            'stagiaires_professionnels' => \App\Models\Stagiaire::where('type_stage', 'professionnel')->count(),
            'stagiaires_en_cours' => \App\Models\Stagiaire::where('statut', 'en_cours')->count(),
            'stagiaires_termines' => \App\Models\Stagiaire::where('statut', 'termine')->count(),
            'documents_en_attente' => \App\Models\Document::where('statut', 'en_attente')->count(),
            'absences_en_attente' => \App\Models\Absence::where('statut', 'en_attente')->count(),
            'rendez_vous_a_venir' => \App\Models\RendezVous::where('date_heure', '>=', now())
                ->where('statut', 'planifie')->count(),
        ]);
    }
}
