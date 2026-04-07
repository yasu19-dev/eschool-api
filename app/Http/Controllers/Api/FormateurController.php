<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Note;
use Illuminate\Http\Request;

class FormateurController extends Controller
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
    public function store(Request $request)
    {
        //
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
    // app/Http/Controllers/Api/FormateurController.php

public function updateProfile(Request $request)
{
    $profile = $request->user()->formateurProfile;

    $validated = $request->validate([
        'telephone' => 'nullable|string|max:20',
        'adresse' => 'nullable|string|max:255',
        'bio' => 'nullable|string',
        'email_professionnel' => 'required|email|max:150',
    ]);

    $profile->update($validated);

    return response()->json([
        'message' => 'Profil mis à jour avec succès',
        'profile' => $profile
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function getSeances(Request $request) {
        return response()->json($request->user()->formateurProfile->seances()->with(['module', 'groupe'])->get());
    }
    

    public function storeAbsences(Request $request) {
        $validated = $request->validate([
            'seance_id' => 'required|exists:seances,id',
            'list' => 'required|array'
        ]);

        foreach ($validated['list'] as $item) {
            Absence::updateOrCreate(
                ['seance_id' => $validated['seance_id'], 'stagiaire_id' => $item['id']],
                ['est_en_retard' => $item['retard'], 'est_justifie' => false]
            );
        }
        return response()->json(['message' => 'Absences enregistrées']);
    }

    public function storeNote(Request $request) {
        $data = $request->validate(['stagiaire_id' => 'required', 'module_id' => 'required', 'valeur' => 'required|numeric']);
        return Note::create($data);
    }
}
