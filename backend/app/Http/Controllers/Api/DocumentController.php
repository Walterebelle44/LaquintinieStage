<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\VerifieAccesStagiaire;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Stagiaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    use VerifieAccesStagiaire;

    public function index(Request $request, Stagiaire $stagiaire)
    {
        $this->verifierAccesStagiaire($request, $stagiaire);

        return response()->json($stagiaire->documents()->latest()->get());
    }

    // Dépôt d'un document par un encadrant/admin pour le compte d'un stagiaire
    public function store(Request $request, Stagiaire $stagiaire)
    {
        $this->verifierAccesStagiaire($request, $stagiaire);

        return response()->json($this->enregistrerDocument($request, $stagiaire), 201);
    }

    // Dépôt d'un document par le stagiaire connecté, pour son propre dossier
    // (route /mon-espace/documents — pas de paramètre {stagiaire} manipulable)
    public function deposerMonDocument(Request $request)
    {
        $stagiaire = $request->user()->stagiaire;
        abort_unless($stagiaire, 404, "Aucune fiche stagiaire associée à ce compte.");

        return response()->json($this->enregistrerDocument($request, $stagiaire), 201);
    }

    private function enregistrerDocument(Request $request, Stagiaire $stagiaire): Document
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['convention', 'rapport', 'memoire', 'attestation', 'livrable', 'autre'])],
            'titre' => 'required|string|max:150',
            'fichier' => 'required|file|max:20480|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        $chemin = $request->file('fichier')->store("stagiaires/{$stagiaire->id}/documents", 'public');

        $document = $stagiaire->documents()->create([
            'type' => $data['type'],
            'titre' => $data['titre'],
            'chemin_fichier' => $chemin,
            'taille_fichier' => $request->file('fichier')->getSize(),
            'statut' => Document::STATUT_EN_ATTENTE,
            'depose_par_id' => $request->user()->id,
        ]);

        activity('document')->causedBy($request->user())->performedOn($document)
            ->log("Dépôt du document « {$data['titre']} »");

        return $document;
    }

    // Validation / rejet par l'encadrant ou l'admin (dont attestation de fin de stage)
    public function traiter(Request $request, Document $document)
    {
        $this->verifierAccesStagiaire($request, $document->stagiaire);

        $data = $request->validate([
            'statut' => ['required', Rule::in([Document::STATUT_VALIDE, Document::STATUT_REJETE])],
            'commentaire' => 'nullable|string|max:255',
        ]);

        $document->update([...$data, 'valide_par_id' => $request->user()->id]);

        activity('document')->causedBy($request->user())->performedOn($document)
            ->log('Document '.($data['statut'] === 'valide' ? 'validé' : 'rejeté'));

        return response()->json($document);
    }

    public function telecharger(Request $request, Document $document)
    {
        $this->verifierAccesStagiaire($request, $document->stagiaire);

        abort_unless(Storage::disk('public')->exists($document->chemin_fichier), 404);

        return Storage::disk('public')->download($document->chemin_fichier, $document->titre);
    }

    public function destroy(Request $request, Document $document)
    {
        Storage::disk('public')->delete($document->chemin_fichier);
        $document->delete();

        return response()->json(['message' => 'Document supprimé.']);
    }
}