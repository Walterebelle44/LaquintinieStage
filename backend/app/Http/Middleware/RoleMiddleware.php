<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Autorise l'accès seulement aux rôles listés.
     * Utilisation dans les routes : ->middleware('role:admin,encadrant')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        if (! $user->peutSeConnecter()) {
            return response()->json(['message' => 'Ce compte est désactivé ou bloqué.'], 403);
        }

        if (! empty($roles) && ! in_array($user->role, $roles, true)) {
            return response()->json(['message' => "Accès refusé : rôle insuffisant."], 403);
        }

        return $next($request);
    }
}
