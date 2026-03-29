<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- IMPORTATIONS DE YASMINE ---
use App\Http\Controllers\AuthController;

// --- IMPORTATIONS DE ZAID ---
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\FormateurController;
use App\Http\Controllers\Api\StagiaireController;
use App\Http\Controllers\Api\PublicController;

/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES (Accessibles sans être connecté)
|--------------------------------------------------------------------------
*/

// 1. Tests et Authentification (Yasmine)
Route::get('/test-connexion', function () {
    return response()->json([
        'statut' => 'Succès',
        'message' => 'L\'API de la plateforme E-School est bien connectée à React !'
    ]);
});
Route::post('/login', [AuthController::class, 'login']);

// 2. Données Publiques (Zaid)
Route::prefix('public')->group(function () {
    Route::get('/filieres', [PublicController::class, 'getFilieres']);
    Route::get('/actualites', [PublicController::class, 'getActualites']);
});


/*
|--------------------------------------------------------------------------
| ROUTES PROTÉGÉES (Nécessitent un token Sanctum validé)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- GESTION DE COMPTE (Yasmine) ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);


    // --- ESPACE ADMIN (Zaid) ---
    Route::prefix('admin')->group(function () {
        Route::get('/statistiques', [AdminController::class, 'getStatistiques']);
        Route::get('/utilisateurs', [AdminController::class, 'getUtilisateurs']);
        Route::post('/import-excel', [AdminController::class, 'importExcel']);
    });


    // --- ESPACE FORMATEUR (Zaid) ---
    Route::prefix('formateur')->group(function () {
        Route::get('/seances', [FormateurController::class, 'getSeances']);
        Route::post('/presences', [FormateurController::class, 'marquerPresence']);
        Route::get('/notes', [FormateurController::class, 'getNotes']);
        Route::post('/notes', [FormateurController::class, 'storeNote']);
        Route::post('/annonces', [FormateurController::class, 'storeAnnonce']);
    });


    // --- ESPACE STAGIAIRE (Zaid) ---
    Route::prefix('stagiaire')->group(function () {
        Route::get('/notes', [StagiaireController::class, 'getNotes']);
        Route::get('/absences', [StagiaireController::class, 'getAbsences']);
        Route::get('/emploi-du-temps', [StagiaireController::class, 'getEmploiDuTemps']);
        Route::post('/demande-attestation', [StagiaireController::class, 'demanderAttestation']);
    });

});
