<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\StagiaireController;
use App\Http\Controllers\Api\FormateurController;
use App\Http\Controllers\Api\DirectorController;
use App\Http\Controllers\Api\ResponsableController;
use App\Http\Controllers\Api\AbsenceController;
use App\Http\Controllers\Api\AnnonceController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\PublicController;

// --- 🔓 ROUTES PUBLIQUES ---
Route::post('/login', [AuthController::class, 'login']);

Route::get('/test-connexion', function () {
    return response()->json(['message' => 'API ISMONTIC connectée !']);
});
Route::get('/filieres', [PublicController::class, 'getFilieres']);
Route::get('/faq', [PublicController::class, 'getFaq']);

// --- 🔐 ROUTES PROTÉGÉES (Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {

    // 👤 Session & Profil
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // 🎓 ESPACE STAGIAIRE
    Route::prefix('stagiaire')->group(function () {
        Route::get('/dashboard', [StagiaireController::class, 'index']);
        Route::get('/notes', [StagiaireController::class, 'getNotes']);
        Route::get('/schedule', [StagiaireController::class, 'getSchedule']);
        Route::post('/reclamations', [StagiaireController::class, 'postReclamation']);
        Route::post('/attestations', [StagiaireController::class, 'postAttestation']);
        // Route::get('/absences', [AbsenceController::class, 'myAbsences']);
        Route::get('/absences', [StagiaireController::class, 'getAbsences']);
        Route::get('/profile', [StagiaireController::class, 'show']);
        Route::put('/profile', [StagiaireController::class, 'update']);
        Route::get('/modules', [StagiaireController::class, 'getModules']);
        Route::get('/schedule', [StagiaireController::class, 'getEmploi']);
        Route::get('/annonces', [AnnonceController::class, 'getStagiaireAnnonces']);
    });

    // 👨‍🏫 ESPACE FORMATEUR
    Route::prefix('formateur')->group(function () {
    Route::get('/profile', [FormateurController::class, 'showProfile']);
    Route::get('/profile-details', [FormateurController::class, 'me']); // Utilise la méthode me existante
    Route::put('/profile/update', [FormateurController::class, 'updateProfile']);
    Route::post('/upload-photo', [FormateurController::class, 'uploadPhoto']);
    Route::get('/dashboard', [FormateurController::class, 'index']);
    Route::get('/seances', [FormateurController::class, 'getSeances']);
    Route::post('/absences/store', [FormateurController::class, 'storeAbsences']);
    Route::post('/notes/store', [FormateurController::class, 'storeNote']);
    Route::post('/absences', [AbsenceController::class, 'store']); // Saisie des absences
    // Route::post('/notes', [NoteController::class, 'store']);
    Route::get('/notes', [NoteController::class, 'getNotesForFormateur']);
    Route::post('/annonces', [AnnonceController::class, 'store']);
    Route::put('/notes/{id}', [NoteController::class, 'update']);
    Route::post('/notes/bulk', [NoteController::class, 'storeBulk']);
    Route::get('/groupes/{groupe_id}/stagiaires', [StagiaireController::class, 'getByGroupe']);
    Route::put('/settings', [FormateurController::class, 'updateSettings']);

    });

    // 🏛️ ESPACE DIRECTION
    Route::prefix('director')->group(function () {
        Route::get('/dashboard', [DirectorController::class, 'index']);
        Route::get('/stats/absences', [AbsenceController::class, 'globalStats']);
        // Gère index, store, show, update, destroy automatiquement
        Route::apiResource('users', UserController::class);
        Route::post('/import/stagiaires', [ImportController::class, 'importStagiaires']);
        Route::delete('/users/{user}', [DirectorController::class, 'deleteUser']);
    });

    // 📋 ESPACE RESPONSABLE STAGIAIRE
    Route::prefix('responsable-stagiaire')->group(function () {
        Route::get('/dashboard', [ResponsableController::class, 'index']);
        Route::get('/justifications', [ResponsableController::class, 'getPendingJustifications']);
        // Route pour valider une absence spécifique
        // Route::patch('/absences/{absence}/validate', [AbsenceController::class, 'validateAbsence']);
        // ✅ CORRECTION :
        // Et utilise {id} pour correspondre à l'argument de ta fonction
        Route::patch('/absences/{id}/validate', [ResponsableController::class, 'validateAbsence']);
        Route::get('/attestations', [ResponsableController::class, 'getPendingAttestations']);

        Route::patch('/attestations/{id}/validate', [ResponsableController::class, 'validateAttestation']);
    });
});
