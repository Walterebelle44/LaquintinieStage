<?php

use App\Http\Controllers\Api\Admin\ActivityLogController;
use App\Http\Controllers\Api\Admin\UserManagementController;
use App\Http\Controllers\Api\AbsenceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\EtablissementController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\MonEspaceController;
use App\Http\Controllers\Api\PresenceController;
use App\Http\Controllers\Api\RendezVousController;
use App\Http\Controllers\Api\StagiaireController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/mon-compte/mot-de-passe', [AuthController::class, 'updatePassword']);

    // ---------- Espace stagiaire ----------
    Route::middleware('role:stagiaire')->group(function () {
        Route::get('/mon-espace/feuille-de-route', [MonEspaceController::class, 'feuilleDeRoute']);
        Route::post('/mon-espace/pointage/arrivee', [PresenceController::class, 'pointerArrivee']);
        Route::post('/mon-espace/pointage/depart', [PresenceController::class, 'pointerDepart']);
        Route::post('/mon-espace/absences', [AbsenceController::class, 'store']);
        Route::post('/mon-espace/documents', [DocumentController::class, 'deposerMonDocument']);
    });

    // ---------- Espace commun admin + encadrant ----------
    Route::middleware('role:admin,encadrant')->group(function () {
        Route::apiResource('stagiaires', StagiaireController::class)->except(['destroy']);
        Route::get('/stagiaires/{stagiaire}/presences', [PresenceController::class, 'index']);
        Route::put('/stagiaires/{stagiaire}/presences/{presence}', [PresenceController::class, 'update']);
        Route::get('/stagiaires/{stagiaire}/absences', [AbsenceController::class, 'index']);
        Route::put('/absences/{absence}/traiter', [AbsenceController::class, 'traiter']);
        Route::get('/stagiaires/{stagiaire}/evaluations', [EvaluationController::class, 'index']);
        Route::post('/stagiaires/{stagiaire}/evaluations', [EvaluationController::class, 'store']);
        Route::put('/stagiaires/{stagiaire}/evaluations/{evaluation}', [EvaluationController::class, 'update']);
        Route::get('/stagiaires/{stagiaire}/documents', [DocumentController::class, 'index']);
        Route::post('/stagiaires/{stagiaire}/documents', [DocumentController::class, 'store']);
        Route::put('/documents/{document}/traiter', [DocumentController::class, 'traiter']);
        Route::get('/documents/{document}/telecharger', [DocumentController::class, 'telecharger']);
        Route::get('/rendez-vous', [RendezVousController::class, 'index']);
        Route::post('/stagiaires/{stagiaire}/rendez-vous', [RendezVousController::class, 'store']);
        Route::put('/rendez-vous/{rendezVous}', [RendezVousController::class, 'update']);
        Route::get('/etablissements', [EtablissementController::class, 'index']);
    });

    // ---------- Réservé admin ----------
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [UserManagementController::class, 'dashboard']);
        Route::apiResource('utilisateurs', UserManagementController::class);
        Route::put('/utilisateurs/{user}/bloquer', [UserManagementController::class, 'bloquer']);
        Route::put('/utilisateurs/{user}/debloquer', [UserManagementController::class, 'debloquer']);
        Route::put('/utilisateurs/{user}/restreindre', [UserManagementController::class, 'restreindre']);
        Route::put('/utilisateurs/{user}/reactiver', [UserManagementController::class, 'reactiver']);
        Route::put('/utilisateurs/{user}/reinitialiser-mot-de-passe', [UserManagementController::class, 'resetPassword']);
        Route::get('/logs', [ActivityLogController::class, 'index']);
        Route::delete('/stagiaires/{stagiaire}', [StagiaireController::class, 'destroy']);
        Route::put('/stagiaires/{stagiaire}/assigner-encadrant', [StagiaireController::class, 'assignerEncadrant']);
        Route::post('/etablissements', [EtablissementController::class, 'store']);
        Route::put('/etablissements/{etablissement}', [EtablissementController::class, 'update']);
        Route::delete('/etablissements/{etablissement}', [EtablissementController::class, 'destroy']);
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);
    });
});