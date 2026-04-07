<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as FacadesDB;

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
        'type_evaluation' => 'required|in:cc1,cc2,efm',
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
    $note = Note::create([
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
    // NoteController.php

// app/Http/Controllers/Api/NoteController.php

public function storeBulk(Request $request)
{
    $validated = $request->validate([
        'module_id'       => 'required|exists:modules,id',
        'type_evaluation' => 'required|in:cc1,cc2,efm', // Uniquement le type d'évaluation
        'session'         => 'required|string',
        'notes'           => 'required|array',
        'notes.*.stagiaire_id' => 'required|exists:stagiaire_profiles,id',
        'notes.*.valeur'       => 'nullable|numeric|min:0|max:20',
    ]);

    $formateurId = $request->user()->formateurProfile->id;

    FacadesDB::transaction(function () use ($validated, $formateurId) {
        foreach ($validated['notes'] as $noteData) {
            if (isset($noteData['valeur']) && $noteData['valeur'] !== '') {
                Note::updateOrCreate(
                    [
                        'stagiaire_id'    => $noteData['stagiaire_id'],
                        'module_id'       => $validated['module_id'],
                        'type_evaluation' => $validated['type_evaluation'],
                    ],
                    [
                        'formateur_id'    => $formateurId,
                        'valeur'          => $noteData['valeur'],
                        'session'         => $validated['session'], // Le semestre est supprimé d'ici
                    ]
                );
            }
        }
    });

    return response()->json(['message' => 'Notes enregistrées avec succès !']);
}
}
