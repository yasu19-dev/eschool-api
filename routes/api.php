<?php

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
