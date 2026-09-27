<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['app' => config('app.name'), 'status' => 'ok']);
});

// Cette API n'a pas de page de connexion web (le frontend Nuxt gère la connexion).
// Cette route nommée 'login' existe uniquement pour éviter un crash Laravel :
// quand une requête arrive sans authentification valide via une navigation
// classique du navigateur (ex: clic sur un lien de téléchargement), le
// middleware d'authentification tente de rediriger vers route('login'). Sans
// cette route, ça provoquait une erreur 500 au lieu d'un message clair.
Route::get('/login', function () {
    return response()->json(['message' => 'Non authentifié.'], 401);
})->name('login');
