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

use App\Http\Controllers\Api\EmploiController;
use App\Http\Controllers\Api\GroupeController; // Assurez-vous que ce controller existe
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\SeanceController;

// --- 🔓 ROUTES PUBLIQUES ---
Route::post('/login', [AuthController::class, 'login']);

Route::get('/test-connexion', function () {
    return response()->json(['message' => 'API ISMONTIC connectée !']);
});

Route::get('/public/filieres', [PublicController::class, 'getFilieres']);
Route::post('/public/contact', [PublicController::class, 'postContact']);

// --- 🔐 ROUTES PROTÉGÉES (Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {

    // 👤 Session & Profil
    Route::get('/me', [AuthController::class, 'me']);
         // ? Route pour réinitialiser le mot de passe d'un utilisateur (par exemple, en cas d'oubli)
Route::put('/director/users/{user}/reset-password', [UserController::class, 'resetPassword']);
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
        Route::get('/mes-seances', [EmploiController::class, 'getMesSeances']);
    });

    // 👨‍🏫 ESPACE FORMATEUR
    Route::prefix('formateur')->group(function () {
    Route::get('/profile', [FormateurController::class, 'showProfile']);
    Route::get('/profile-details', [FormateurController::class, 'me']); // Utilise la méthode me existante
    Route::put('/profile/update', [FormateurController::class, 'updateProfile']);
    Route::post('/upload-photo', [FormateurController::class, 'uploadPhoto']);
    Route::get('/dashboard', [FormateurController::class, 'index']);
    Route::post('/annonces', [AnnonceController::class, 'store']);
    Route::get('/seances', [FormateurController::class, 'getSeances']);
    Route::post('/absences/store', [FormateurController::class, 'storeAbsences']);
    Route::post('/notes/store', [FormateurController::class, 'storeNote']);
    // Route::post('/absences', [AbsenceController::class, 'store']); // Saisie des absences
    // Route::post('/notes', [NoteController::class, 'store']);
    Route::get('/notes', [NoteController::class, 'getNotesForFormateur']);
    Route::put('/notes/{id}', [NoteController::class, 'update']);
    Route::post('/notes/bulk', [NoteController::class, 'storeBulk']);
    Route::get('/groupes/{groupe_id}/stagiaires', [StagiaireController::class, 'getByGroupe']);
    // Route::get('/groupes', [FormateurController::class, 'getGroupes']);
    Route::put('/settings', [FormateurController::class, 'updateSettings']);
    Route::post('/absences/bulk', [AbsenceController::class, 'storeBulk']);
    Route::get('/absences/historique', [AbsenceController::class, 'history']);
    Route::post('/absences/details', [AbsenceController::class, 'getAbsencesBySession']);
    // Routes pour les filtres des statistiques
    Route::get('/groupes', [FormateurController::class, 'getGroupes']);
    Route::get('/modules', [FormateurController::class, 'getFormateurModules']);

    // Route principale pour les données de statistiques
    Route::get('/statistics', [FormateurController::class, 'getStatistics']);
    Route::get('/profile-stats', [FormateurController::class, 'getProfileStats']);

    Route::get('/absences-recentes', [AbsenceController::class, 'recent']);
    Route::get('/mes-seances', [FormateurController::class, 'mesSeances']);

    });

    // 🏛️ ESPACE DIRECTION
    Route::prefix('director')->group(function () {
    Route::get('/users', [DirectorController::class, 'getUsers']);
    Route::get('/users/trashed', [DirectorController::class, 'trashed']); // ✅ Doit s'appeler 'trashed'
    Route::post('/users', [DirectorController::class, 'store']);
    Route::delete('/users/{user}', [DirectorController::class, 'deleteUser']);
    Route::put('/users/{id}/restore', [DirectorController::class, 'restore']);

    Route::get('/groupes', [DirectorController::class, 'getGroupes']);
    Route::get('/specialites', [DirectorController::class, 'getSpecialites']);
    Route::post('/import-stagiaires', [ImportController::class, 'importStagiaires']);
    Route::post('/import-timetable', [ImportController::class, 'importTimetable']);
    // ✅ Ajoute ces deux routes ici :
Route::get('/groupes/{id}/seances', [DirectorController::class, 'getSeancesByGroupe']);
Route::get('/formateurs/{id}/seances', [DirectorController::class, 'getSeancesByFormateur']);
Route::delete('/users/{id}/force-delete', [DirectorController::class, 'forceDelete']);
    // Ta route reset password peut rester dans UserController si tu veux
    Route::put('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
});

    // 📋 ESPACE RESPONSABLE STAGIAIRE
    Route::prefix('responsable-stagiaire')->group(function () {
        Route::get('/dashboard', [ResponsableController::class, 'index']);
        Route::get('/justifications', [ResponsableController::class, 'getPendingJustifications']);
        Route::patch('/absences/{id}/validate', [ResponsableController::class, 'validateAbsence']);
        // Routes pour la gestion des attestations:
        Route::get('/attestations', [ResponsableController::class, 'getAttestations']);
        Route::patch('/attestations/{id}/status', [ResponsableController::class, 'updateStatus']);
        Route::get('/attestations/{id}/generate-pdf', [ResponsableController::class, 'generatePdf']);
        Route::get('/contacts', [PublicController::class, 'getMessagesForAdmin']);
        Route::patch('/contacts/{contact}/read', [PublicController::class, 'markAsRead']);
        Route::delete('/contacts/{contact}', [PublicController::class, 'destroy']);

        // Optionnel : voir le dernier emploi uploadé
        Route::get('/stagiaire/emploi-du-temps', [EmploiController::class, 'getLatest']);
    });
});
