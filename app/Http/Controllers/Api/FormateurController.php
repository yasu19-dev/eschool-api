<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Groupe;
use App\Models\Note;
use App\Models\Seance;
use App\Models\StagiaireProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    // // On récupère les groupes qui ont au moins une séance avec ce formateur
    // $groupes = Groupe::whereHas('seances', function($query) use ($formateurId) {
    //     $query->where('formateur_id', $formateurId);
    // })->get();
    $groupes = Seance::where('formateur_id', $formateurId)
            ->with('groupe')
            ->get()
            ->pluck('groupe')
            ->unique('id')
            ->values();
    // $groupes = Groupe::all();

    return response()->json($groupes);
}
public function getModules(Request $request) {
    // On récupère les modules liés aux séances de CE formateur
    $modules = Seance::where('formateur_id', $request->user()->formateurProfile->id)
        ->with('module')
        ->get()
        ->pluck('module')
        ->unique('id')
        ->values();


    return response()->json($modules);
}

public function getStatistics(Request $request)
    {
        $formateurId = $request->user()->formateurProfile->id;
        $groupeId = $request->query('groupe_id');
        $moduleId = $request->query('module_id');
        $periode = $request->query('periode', 'semester');

        // --- A. Filtrage de base des séances ---
        $seanceQuery = Seance::where('formateur_id', $formateurId);
        if ($groupeId) $seanceQuery->where('groupe_id', $groupeId);
        if ($moduleId) $seanceQuery->where('module_id', $moduleId);

        $seanceIds = $seanceQuery->pluck('id');
        $groupeIds = $seanceQuery->pluck('groupe_id')->unique();

        // --- B. Global Stats ---
        $totalEtudiants = StagiaireProfile::whereIn('groupe_id', $groupeIds)->count();
        $moyenneG = Note::whereIn('module_id', $seanceQuery->pluck('module_id'))->avg('valeur') ?: 0;

        // Taux de présence (Logique simplifiée)
        $totalAppels = DB::table('absence_stagiaire') // Ta table pivot d'absences
            ->whereIn('seance_id', $seanceIds)->count();
        $totalAbsences = DB::table('absence_stagiaire')
            ->whereIn('seance_id', $seanceIds)->where('est_en_retard', false)->count();

        $tauxPresence = $totalAppels > 0 ? round(100 - (($totalAbsences / $totalAppels) * 100), 1) : 100;

        // --- C. Moyennes Data (Évolution par mois) ---
        $moyennesData = Note::whereIn('module_id', $seanceQuery->pluck('module_id'))
            ->select(DB::raw('MONTHNAME(created_at) as mois'), DB::raw('AVG(valeur) as moyenne'))
            ->groupBy('mois')
            ->orderBy('created_at')
            ->get();

        // --- D. Top Students ---
        $topStudents = StagiaireProfile::whereIn('groupe_id', $groupeIds)
            ->with(['user', 'groupe'])
            ->get()
            ->map(function($s) {
                return [
                    'name' => $s->nom . ' ' . $s->prenom,
                    'groupe' => $s->groupe->code,
                    'moyenne' => round(Note::where('stagiaire_id', $s->id)->avg('valeur'), 2) ?: 0
                ];
            })->sortByDesc('moyenne')->take(5)->values();

        // --- E. Mentions Data ---
        $notes = Note::whereIn('module_id', $seanceQuery->pluck('module_id'))->pluck('valeur');
        $mentions = [
            ['name' => 'Excellent', 'value' => $notes->where('>=', 16)->count(), 'color' => '#00C9A7'],
            ['name' => 'Très bien', 'value' => $notes->whereBetween('valeur', [14, 15.99])->count(), 'color' => '#1E88E5'],
            ['name' => 'Bien', 'value' => $notes->whereBetween('valeur', [12, 13.99])->count(), 'color' => '#FF9800'],
            ['name' => 'Passable', 'value' => $notes->whereBetween('valeur', [10, 11.99])->count(), 'color' => '#9C27B0'],
            ['name' => 'Insuffisant', 'value' => $notes->where('<', 10)->count(), 'color' => '#EF5350'],
        ];

        return response()->json([
            'globalStats' => [
                'totalEtudiants' => $totalEtudiants,
                'moyenneGénérale' => round($moyenneG, 2),
                'tauxPresence' => $tauxPresence,
                'tauxReussite' => 85 // Exemple statique ou calculé sur notes > 10
            ],
            'moyennesData' => $moyennesData,
            'presenceData' => [
                ['module' => 'Global', 'present' => $tauxPresence, 'absent' => 100 - $tauxPresence]
            ],
            'notesDistribution' => [
                ['range' => '0-10', 'count' => $notes->where('<', 10)->count()],
                ['range' => '10-14', 'count' => $notes->whereBetween('valeur', [10, 14])->count()],
                ['range' => '14-20', 'count' => $notes->where('>=', 14)->count()],
            ],
            'mentionsData' => $mentions,
            'topStudents' => $topStudents,
            'strugglingStudents' => [], // À calculer selon le même principe (moyenne < 10)
            'difficultModules' => [],
            'successModules' => [],
            'absencesTendances' => []
        ]);
    }
}
