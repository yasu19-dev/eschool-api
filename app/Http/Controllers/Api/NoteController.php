<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class NoteController extends Controller
{
    /**
     * Enregistrer une seule note (CREATE uniquement)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'stagiaire_id'    => 'required|exists:stagiaire_profiles,id',
            'module_id'       => 'required|exists:modules,id',
            'valeur'          => 'required|numeric|min:0|max:20',
            'type_evaluation' => 'required|in:cc1,cc2,efm',
            'session'         => 'required|string',
        ]);

        $formateur = $request->user()->formateurProfile;

        if (!$formateur) {
            return response()->json(['message' => "Profil formateur non trouvé."], 403);
        }

        // Vérifier si la note existe déjà pour éviter les doublons en mode "Create"
        $exists = Note::where([
            'stagiaire_id'    => $validated['stagiaire_id'],
            'module_id'       => $validated['module_id'],
            'type_evaluation' => $validated['type_evaluation'],
        ])->exists();

        if ($exists) {
            return response()->json(['message' => 'Une note existe déjà pour ce module et ce type.'], 422);
        }

        $note = Note::create([
            'stagiaire_id'    => $validated['stagiaire_id'],
            'module_id'       => $validated['module_id'],
            'formateur_id'    => $formateur->id,
            'valeur'          => $validated['valeur'],
            'type_evaluation' => $validated['type_evaluation'],
            'session'         => $validated['session'],
        ]);

        return response()->json(['message' => 'Note créée avec succès !', 'data' => $note], 201);
    }

    /**
     * Mettre à jour une seule note (UPDATE uniquement)
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'valeur'  => 'required|numeric|min:0|max:20',
            'session' => 'sometimes|string',
        ]);

        $note = Note::findOrFail($id);

        $note->update([
            'valeur'  => $validated['valeur'],
            'session' => $validated['session'] ?? $note->session,
        ]);

        return response()->json(['message' => 'Note mise à jour !', 'data' => $note], 200);
    }

    /**
     * Enregistrement en masse (Logique manuelle Create ou Update)
     */
    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'module_id'       => 'required|exists:modules,id',
            'type_evaluation' => 'required|in:cc1,cc2,efm',
            'session'         => 'required|string',
            'notes'           => 'required|array',
            'notes.*.stagiaire_id' => 'required|exists:stagiaire_profiles,id',
            'notes.*.valeur'       => 'nullable|numeric|min:0|max:40',
        ]);

        $formateurId = $request->user()->formateurProfile->id;

        DB::transaction(function () use ($validated, $formateurId) {
            foreach ($validated['notes'] as $noteItem) {
                if ($noteItem['valeur'] !== null && $noteItem['valeur'] !== '') {

                    // 1. On cherche manuellement si la note existe
                    $note = Note::where([
                        'stagiaire_id'    => $noteItem['stagiaire_id'],
                        'module_id'       => $validated['module_id'],
                        'type_evaluation' => $validated['type_evaluation'],
                    ])->first();

                    if ($note) {
                        // 2. Si elle existe, on fait un UPDATE
                        $note->update([
                            'valeur'       => $noteItem['valeur'],
                            'formateur_id' => $formateurId,
                            'session'      => $validated['session'],
                        ]);
                    } else {
                        // 3. Sinon, on fait un CREATE
                        Note::create([
                            'stagiaire_id'    => $noteItem['stagiaire_id'],
                            'module_id'       => $validated['module_id'],
                            'formateur_id'    => $formateurId,
                            'type_evaluation' => $validated['type_evaluation'],
                            'valeur'          => $noteItem['valeur'],
                            'session'         => $validated['session'],
                        ]);
                    }
                }
            }
        });

        return response()->json(['message' => 'Traitement bulk terminé avec succès !'], 200);
    }
    public function getNotesForFormateur(Request $request)
{
    // On valide que les paramètres sont présents dans l'URL
    $request->validate([
        'module_id' => 'required|exists:modules,id',
        'type'      => 'required|in:cc1,cc2,efm',
    ]);

    // On récupère les notes filtrées
    $notes = Note::where('module_id', $request->module_id)
                 ->where('type_evaluation', $request->type)
                 ->get();

    return response()->json($notes);
}
}
