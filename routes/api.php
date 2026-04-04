    <?php

    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\AbsenceController;
    use App\Http\Controllers\SeanceController;
    use App\Http\Controllers\UserController;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');


    Route::get('/test-connexion', function () {
        return response()->json([
            'statut' => 'Succès',
            'message' => 'L\'API de la plateforme E-School est bien connectée à React !'
        ]);
    });

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

     //  ESPACE STAGIAIRE
    Route::prefix('stagiaire')->group(function () {
        Route::get('/absences', [AbsenceController::class, 'myAbsences']);
        Route::get('/schedule', [SeanceController::class, 'forStagiaire']);
    });

    //  ESPACE FORMATEUR
    Route::prefix('formateur')->group(function () {
        Route::get('/seances', [SeanceController::class, 'index']);
        Route::post('/absences', [AbsenceController::class, 'store']); // Saisie
    });

    //  ESPACE DIRECTION
    Route::prefix('director')->group(function () {
        Route::get('/stats/global', [AbsenceController::class, 'globalStats']);
        Route::apiResource('users', UserController::class); // CRUD complet
    });

    //  ESPACE RESPONSABLE STAGIAIRE
    Route::prefix('responsable-stagiaire')->group(function () {
        Route::get('/justifications', [AbsenceController::class, 'pendingJustifications']);
        Route::patch('/absences/{id}', [AbsenceController::class, 'validateJustification']);
    });
