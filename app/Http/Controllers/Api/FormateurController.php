<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
    // app/Http/Controllers/Api/FormateurController.php

public function showProfile(Request $request)
{
    // On récupère l'utilisateur et on charge son profil formateur
    $user = $request->user();

    // On s'assure de charger la relation 'formateurProfile'
    $user->load('formateurProfile');

    if (!$user->formateurProfile) {
        return response()->json(['message' => 'Profil formateur non trouvé.'], 404);
    }

    // On renvoie les données (ton frontend attend cet objet)
    return response()->json($user->formateurProfile);
}

    /**
     * Update the specified resource in storage.
     */
    // app/Http/Controllers/Api/FormateurController.php

// app/Http/Controllers/Api/FormateurController.php

public function updateProfile(Request $request)
{
    // 1. Validation des champs éditables
    $validated = $request->validate([
        'adresse'             => 'nullable|string|max:255',
        'email_professionnel' => 'nullable|email|max:150',
        'telephone'           => 'nullable|string|max:20',
        'bio'                 => 'nullable|string',
    ]);

    // 2. Récupération du profil du formateur connecté
    $profile = $request->user()->formateurProfile;

    if (!$profile) {
        return response()->json(['message' => 'Profil non trouvé'], 404);
    }

    // 3. Mise à jour en base de données
    $profile->update($validated);

    return response()->json([
        'message' => 'Profil mis à jour avec succès !',
        'profile' => $profile
    ], 200);
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
    public function uploadPhoto(Request $request)
{
    $request->validate([
        'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
    ]);

    $user = $request->user();
    $profile = $user->formateurProfile;

    if ($request->hasFile('photo')) {
        // 1. Supprimer l'ancienne photo si elle existe pour ne pas encombrer le serveur
        if ($profile->photo_url) {
            $oldPath = str_replace(asset('storage/'), '', $profile->photo_url);
            Storage::disk('public')->delete($oldPath);
        }

        // 2. Stocker la nouvelle photo
        $path = $request->file('photo')->store('profiles', 'public');

        // 3. Mettre à jour l'URL en base de données
        $profile->update([
            'photo_url' => asset('storage/' . $path)
        ]);

        return response()->json([
            'message' => 'Photo téléchargée !',
            'photo_url' => asset('storage/' . $path)
        ]);
    }}

    // app/Http/Controllers/Api/FormateurController.php
public function updateSettings(Request $request)
{
    $validated = $request->validate([
        'email_notifications' => 'required|boolean',
        'profile_visibility' => 'required|boolean',
    ]);

    $profile = $request->user()->formateurProfile;
    $profile->update($validated);

    return response()->json(['message' => 'Paramètres mis à jour !']);
}
}
