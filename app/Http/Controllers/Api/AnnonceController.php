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
    $validated = $request->validate([
        'titre'      => 'required|string|max:200',
        'contenu'    => 'required|string',
        'groupe_id'  => 'nullable|exists:groupes,id', // Si vide = annonce pour tous ses groupes
    ]);

    $annonce = \App\Models\Annonce::create([
        'formateur_id' => $request->user()->formateurProfile->id,
        'groupe_id'    => $validated['groupe_id'],
        'titre'        => $validated['titre'],
        'contenu'      => $validated['contenu'],
        'date_publication' => now(),
    ]);

    return response()->json(['message' => 'Annonce publiée !', 'annonce' => $annonce], 201);
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
