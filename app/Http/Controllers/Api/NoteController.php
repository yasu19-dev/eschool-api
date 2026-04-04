<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;


class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // NoteController.php

public function store(Request $request)
{
    // 1. Validation (Gardons la même)
    $validated = $request->validate([
        'stagiaire_id'    => 'required|exists:stagiaire_profiles,id',
        'module_id'       => 'required|exists:modules,id',
        'valeur'          => 'required|numeric|min:0|max:20',
        'type_evaluation' => 'required|string',
        'session'         => 'required|string',
    ]);

    // 2. Récupération sécurisée du profil formateur
    $formateur = $request->user()->formateurProfile;

    // Si l'utilisateur n'est pas un formateur, on arrête tout proprement
    if (!$formateur) {
        return response()->json([
            'message' => "Erreur : Vous n'avez pas de profil formateur associé."
        ], 403);
    }

    // 3. Création de la note
    $note = \App\Models\Note::create([
        'stagiaire_id'    => $validated['stagiaire_id'],
        'module_id'       => $validated['module_id'],
        'formateur_id'    => $formateur->id, // Plus d'erreur ici !
        'valeur'          => $validated['valeur'],
        'type_evaluation' => $validated['type_evaluation'],
        'session'         => $validated['session'],
    ]);

    return response()->json([
        'message' => 'Note enregistrée avec succès !',
        'data' => $note
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
