<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Groupe;
use App\Models\Note;
use App\Models\Seance;
use App\Models\StagiaireProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // app/Http/Controllers/Api/FormateurController.php

public function index(Request $request)
{
    $formateurId = $request->user()->formateurProfile->id;
    $today = now()->format('yyyy-MM-dd');
    $startOfWeek = now()->startOfWeek()->format('yyyy-MM-dd');
    $endOfWeek = now()->endOfWeek()->format('yyyy-MM-dd');

    // 1. Cours aujourd'hui
    $todayClassesCount = Seance::where('formateur_id', $formateurId)
        ->where('date', $today)
        ->count();

    // 2. Nombre total de stagiaires uniques enseignés
    $totalStudents = StagiaireProfile::whereIn('groupe_id', function($query) use ($formateurId) {
        $query->select('groupe_id')->from('seances')->where('formateur_id', $formateurId);
    })->count();

    // 3. Absences de la semaine
    $weekAbsencesCount = Absence::whereHas('seance', function($q) use ($formateurId) {
        $q->where('formateur_id', $formateurId);
    })->whereBetween('date', [$startOfWeek, $endOfWeek])->count();

    // 4. Prochains cours (Aujourd'hui et futur proche)
    $upcomingClasses = Seance::where('formateur_id', $formateurId)
        ->where('date', '>=', $today)
        ->with(['groupe', 'module'])
        ->orderBy('date', 'asc')
        ->orderBy('creneau', 'asc')
        ->take(4)
        ->get();

    // 5. Absences récentes enregistrées
    $recentAbsences = Absence::whereHas('seance', function($q) use ($formateurId) {
        $q->where('formateur_id', $formateurId);
    })->with(['stagiaire', 'seance.groupe'])
      ->orderBy('date', 'desc')
      ->orderBy('created_at', 'desc')
      ->take(5)
      ->get();

    // 6. Mes modules (calcul du nombre de stagiaires par module)
    $myModules = Seance::where('formateur_id', $formateurId)
        ->with('module')
        ->get()
        ->groupBy('module_id')
        ->map(function ($group) {
            $module = $group->first()->module;
            return [
                'name' => $module->intitule ?? $module->nom,
                'students' => StagiaireProfile::whereIn('groupe_id', $group->pluck('groupe_id'))->count(),
                'color' => '#' . substr(md5($module->id), 0, 6) // Couleur générée aléatoirement par ID
            ];
        })->values();

    return response()->json([
        'stats' => [
            'todayClasses' => $todayClassesCount,
            'totalStudents' => $totalStudents,
            'weekAbsences' => $weekAbsencesCount,
            'attendanceRate' => '94%', // Exemple statique ou calculable si besoin
        ],
        'upcomingClasses' => $upcomingClasses,
        'recentAbsences' => $recentAbsences,
        'myModules' => $myModules
    ]);
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

    // app/Http/Controllers/Api/FormateurController.php

public function getHistory(Request $request) {
    // On renvoie un tableau vide pour l'instant pour stopper l'erreur 500
    return response()->json([]);
}

    public function storeAbsences(Request $request) {
        $validated = $request->validate([
            'seance_id' => 'required|exists:seances,id',
            'stagiaires' => 'required|array'
        ]);

        foreach ($validated['stagiaires'] as $item) {
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
// app/Http/Controllers/Api/FormateurController.php



public function getGroupes(Request $request)
{
    $formateurId = $request->user()->formateurProfile->id;

    // On récupère les groupes qui ont au moins une séance avec ce formateur
    $groupes = Groupe::whereHas('seances', function($query) use ($formateurId) {
        $query->where('formateur_id', $formateurId);
    })->get();

    return response()->json($groupes);
}
}
