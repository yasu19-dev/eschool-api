<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnnonceController extends Controller
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
    // AnnonceController.php

public function store(Request $request)
{
    try {
        // 1. Validation (On ajoute 'type' avec les valeurs exactes de ton ENUM)
        $validated = $request->validate([
            'titre'     => 'required|string|max:255',
            'contenu'   => 'required|string',
            'type'      => 'required|in:Examen,Absence Formateur,Information',
            'groupe_id' => 'required|exists:groupes,id',
        ]);

        $formateur = $request->user()->formateurProfile;

        if (!$formateur) {
            return response()->json(['error' => 'Profil formateur introuvable'], 403);
        }

        // 2. Création
        $annonce = \App\Models\Annonce::create([
            'formateur_id' => $formateur->id,
            'groupe_id'    => $validated['groupe_id'],
            'titre'        => $validated['titre'],
            'contenu'      => $validated['contenu'],
            'type'         => $validated['type'], // On insère le type ici
        ]);

        return response()->json([
            'message' => 'Annonce publiée avec succès !',
            'data' => $annonce
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'Erreur Annonce',
            'message' => $e->getMessage()
        ], 500);
    }
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
