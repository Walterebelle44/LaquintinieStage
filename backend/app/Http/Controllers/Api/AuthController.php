<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ["Identifiants incorrects."],
            ]);
        }

        if (! $user->peutSeConnecter()) {
            throw ValidationException::withMessages([
                'email' => [$user->is_blocked
                    ? 'Votre compte a été bloqué. Contactez un administrateur.'
                    : 'Votre compte est désactivé.'],
            ]);
        }

        $user->forceFill(['derniere_connexion_at' => now()])->save();

        $token = $user->createToken('api-token')->plainTextToken;

        activity('utilisateur')
            ->causedBy($user)
            ->log('Connexion réussie');

        return response()->json([
            'token' => $token,
            'user' => $user->load('stagiaire'),
        ]);
    }

    public function logout(Request $request)
    {
        activity('utilisateur')->causedBy($request->user())->log('Déconnexion');
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('stagiaire'));
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Mot de passe actuel incorrect.'],
            ]);
        }

        $user->update(['password' => Hash::make($data['password'])]);
        activity('utilisateur')->causedBy($user)->log('Mot de passe modifié');

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }
}
