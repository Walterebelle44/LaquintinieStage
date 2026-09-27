<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\VerifieAccesStagiaire;
use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\Stagiaire;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PresenceController extends Controller
{
    use VerifieAccesStagiaire;

    public function index(Request $request, Stagiaire $stagiaire)
    {
        $this->verifierAccesStagiaire($request, $stagiaire);

        return response()->json(
            $stagiaire->presences()->orderByDesc('date')->paginate($request->integer('per_page', 31))
        );
    }

    // Le stagiaire pointe lui-même son arrivée
    public function pointerArrivee(Request $request)
    {
        $stagiaire = $request->user()->stagiaire;
        abort_unless($stagiaire, 404, "Aucune fiche stagiaire associée à ce compte.");

        $presence = Presence::firstOrCreate(
            ['stagiaire_id' => $stagiaire->id, 'date' => now()->toDateString()],
            ['heure_arrivee' => now()->toTimeString(), 'statut' => now()->format('H:i') > '08:15' ? 'retard' : 'present']
        );

        if (! $presence->wasRecentlyCreated && ! $presence->heure_arrivee) {
            $presence->update([
                'heure_arrivee' => now()->toTimeString(),
                'statut' => now()->format('H:i') > '08:15' ? 'retard' : 'present',
            ]);
        }

        return response()->json($presence);
    }

    public function pointerDepart(Request $request)
    {
        $stagiaire = $request->user()->stagiaire;
        abort_unless($stagiaire, 404, "Aucune fiche stagiaire associée à ce compte.");

        $presence = Presence::where('stagiaire_id', $stagiaire->id)
            ->where('date', now()->toDateString())
            ->first();

        abort_unless($presence, 422, "Vous devez d'abord pointer votre arrivée.");

        $presence->update(['heure_depart' => now()->toTimeString()]);

        return response()->json($presence);
    }

    // Correction manuelle par un encadrant/admin
    public function update(Request $request, Stagiaire $stagiaire, Presence $presence)
    {
        $this->verifierAccesStagiaire($request, $stagiaire);

        $data = $request->validate([
            'heure_arrivee' => 'nullable|date_format:H:i:s',
            'heure_depart' => 'nullable|date_format:H:i:s',
            'statut' => ['required', Rule::in(['present', 'retard', 'absent'])],
            'commentaire' => 'nullable|string|max:255',
        ]);

        $presence->update($data);

        activity('presence')->causedBy($request->user())->performedOn($presence)
            ->log('Correction manuelle de présence');

        return response()->json($presence);
    }
}