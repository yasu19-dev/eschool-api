<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\StagiaireController;
use App\Http\Controllers\Api\FormateurController;
use App\Http\Controllers\Api\DirectorController;
use App\Http\Controllers\Api\ResponsableController;
use App\Http\Controllers\Api\AbsenceController;
use App\Http\Controllers\Api\AnnonceController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\EmploiController;
use App\Http\Controllers\Api\GroupeController; // Assurez-vous que ce controller existe

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
        Route::get('/absences', [StagiaireController::class, 'getAbsences']);
        Route::get('/profile', [StagiaireController::class, 'show']);
        Route::put('/profile', [StagiaireController::class, 'update']);
        Route::get('/modules', [StagiaireController::class, 'getModules']);
        Route::get('/annonces', [AnnonceController::class, 'getStagiaireAnnonces']);
        Route::post('upload-photo', [StagiaireController::class, 'uploadPhoto']);
        Route::post('/reclamations', [StagiaireController::class, 'postReclamation']);
        Route::post('/attestations', [StagiaireController::class, 'postAttestation']);

        // 📅 Emploi du temps (Option B : filtré par le group_id du stagiaire)
        Route::get('/schedule', [EmploiController::class, 'getForStagiaire']);
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

        // 📅 Gestion des Emplois du temps
        // Cette route gère l'importation avec l'option "Tous les groupes" ou sélection multiple
        Route::post('/emplois/import', [EmploiController::class, 'store']);

        // 👥 Gestion des Groupes (pour les checkboxes dans React)
        Route::get('/groupes', [GroupeController::class, 'index']);

        Route::apiResource('users', UserController::class);
        Route::post('/import/stagiaires', [ImportController::class, 'importStagiaires']);
        Route::delete('/users/{user}', [DirectorController::class, 'deleteUser']);
    });

    // 📋 ESPACE RESPONSABLE STAGIAIRE
    Route::prefix('responsable-stagiaire')->group(function () {
        Route::get('/dashboard', [ResponsableController::class, 'index']);
        Route::get('/justifications', [ResponsableController::class, 'getPendingJustifications']);
        Route::patch('/absences/{id}/validate', [ResponsableController::class, 'validateAbsence']);
        Route::get('/attestations', [ResponsableController::class, 'getPendingAttestations']);
        Route::patch('/attestations/{id}/validate', [ResponsableController::class, 'validateAttestation']);

        // Optionnel : voir le dernier emploi uploadé
        Route::get('/stagiaire/emploi-du-temps', [EmploiController::class, 'getLatest']);
    });
});
