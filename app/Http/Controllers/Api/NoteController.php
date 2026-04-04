<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    $validated = $request->validate([
        'stagiaire_id' => 'required|exists:stagiaire_profiles,id',
        'module_id'    => 'required|exists:modules,id',
        'valeur'       => 'required|numeric|min:0|max:20',
        'type_examen'  => 'required|in:Controle Continu,EFM,TP',
    ]);

    $formateurId = $request->user()->formateurProfile->id;

    $note = \App\Models\Note::create([
        'stagiaire_id' => $validated['stagiaire_id'],
        'module_id'    => $validated['module_id'],
        'formateur_id' => $formateurId,
        'valeur'       => $validated['valeur'],
        'type_examen'  => $validated['type_examen'],
        'date_saisie'  => now(),
    ]);

    return response()->json(['message' => 'Note ajoutée avec succès', 'note' => $note], 201);
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
