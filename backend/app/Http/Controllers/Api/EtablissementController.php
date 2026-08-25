<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\Request;

class EtablissementController extends Controller
{
    public function index()
    {
        return response()->json(Etablissement::orderBy('nom')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:150',
            'ville' => 'nullable|string|max:100',
            'filiere' => 'nullable|string|max:150',
            'contact_nom' => 'nullable|string|max:150',
            'contact_email' => 'nullable|email',
            'contact_telephone' => 'nullable|string|max:30',
        ]);

        return response()->json(Etablissement::create($data), 201);
    }

    public function update(Request $request, Etablissement $etablissement)
    {
        $data = $request->validate([
            'nom' => 'sometimes|string|max:150',
            'ville' => 'nullable|string|max:100',
            'filiere' => 'nullable|string|max:150',
            'contact_nom' => 'nullable|string|max:150',
            'contact_email' => 'nullable|email',
            'contact_telephone' => 'nullable|string|max:30',
        ]);

        $etablissement->update($data);

        return response()->json($etablissement);
    }

    public function destroy(Etablissement $etablissement)
    {
        $etablissement->delete();

        return response()->json(['message' => 'Établissement supprimé.']);
    }
}
