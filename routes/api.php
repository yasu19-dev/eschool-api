<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\UserController;

// --- 🔓 ROUTES PUBLIQUES ---
Route::post('/login', [AuthController::class, 'login']);

// --- 🔐 ROUTES PROTÉGÉES (Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {

    // 🎓 Espace Stagiaire
    Route::prefix('stagiaire')->group(function () {
        Route::get('/absences', [AbsenceController::class, 'myAbsences']);
    });

    // 👨‍🏫 Espace Formateur
    Route::prefix('formateur')->group(function () {
        Route::get('/seances', [SeanceController::class, 'index']);
        Route::post('/absences', [AbsenceController::class, 'store']); // Saisie d'absence
    });

    // 🏛️ Espace Direction (Yasmine)
    Route::prefix('director')->group(function () {
        Route::get('/stats/global', [AbsenceController::class, 'globalStats']);
        Route::apiResource('users', UserController::class);
    });

    // 📋 Espace Responsable Stagiaire (Aya)
    Route::prefix('responsable-stagiaire')->group(function () {
        Route::get('/justifications', [AbsenceController::class, 'pendingJustifications']);
        Route::patch('/absences/{absence}', [AbsenceController::class, 'validateJustification']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});
