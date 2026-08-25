<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Stagiaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index(Stagiaire $stagiaire)
    {
        return response()->json($stagiaire->documents()->latest()->get());
    }

    // Dépôt d'un document (convention, rapport, mémoire, livrable...) par le stagiaire ou pour son compte
    public function store(Request $request, Stagiaire $stagiaire)
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

        return response()->json($document, 201);
    }

    // Validation / rejet par l'encadrant ou l'admin (dont attestation de fin de stage)
    public function traiter(Request $request, Document $document)
    {
        $data = $request->validate([
            'statut' => ['required', Rule::in([Document::STATUT_VALIDE, Document::STATUT_REJETE])],
            'commentaire' => 'nullable|string|max:255',
        ]);

        $document->update([...$data, 'valide_par_id' => $request->user()->id]);

        activity('document')->causedBy($request->user())->performedOn($document)
            ->log('Document '.($data['statut'] === 'valide' ? 'validé' : 'rejeté'));

        return response()->json($document);
    }

    public function telecharger(Document $document)
    {
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
