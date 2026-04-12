<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\Note;
use App\Models\Seance;
use App\Models\StagiaireProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
        $filename = basename($profile->photo_url);

    // 2. On détermine le sous-dossier (stagiaires ou formateurs)
    // On vérifie si l'objet $profile est une instance de StagiaireProfile
    $subfolder = ($profile instanceof StagiaireProfile) ? 'stagiaires' : 'formateurs';

    // 3. On construit le chemin complet dans le disque public
    $pathToDelete = "profiles/{$subfolder}/{$filename}";

    // 4. Suppression si le fichier existe
    if (Storage::disk('public')->exists($pathToDelete)) {
        Storage::disk('public')->delete($pathToDelete);
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
    $formateur = $request->user()->formateurProfile;

    // On récupère les groupes directement liés à ce formateur
    // (Aya a dû créer une relation dans ton modèle FormateurProfile)
    $groupes = $formateur->groupes()
        ->select('groupes.id', 'groupes.code')
        ->distinct()
        ->get();

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
public function getFormateurModules(Request $request)
{
    $user = $request->user();

    // On vérifie si le profil formateur existe
    if (!$user->formateurProfile) {
        return response()->json(['error' => 'Profil formateur non trouvé'], 404);
    }

    $formateurId = $user->formateurProfile->id;

    // MÉTHODE ULTIME : On récupère les modules liés aux séances
    $modules = DB::table('seances')
        ->join('modules', 'seances.module_id', '=', 'modules.id')
        ->where('seances.formateur_id', $formateurId)
        ->select('modules.id', 'modules.intitule as label', 'modules.code')
        ->distinct()
        ->get();

    // Log pour le debug Laravel (vérifie ton fichier storage/logs/laravel.log)
    Log::info("Modules pour formateur $formateurId : " . $modules->count());

    return response()->json($modules);
}


public function getStatistics(Request $request)
{
    $formateurProfile = $request->user()->formateurProfile;
    if (!$formateurProfile) return response()->json(['error' => 'Profil non trouvé'], 404);

    $formateurId = $formateurProfile->id;
    $groupeId = $request->query('groupe_id');
    $moduleId = $request->query('module_id');
    $periode = $request->query('periode', 'month');

    // 1. Gestion des dates
    $startDate = null; $endDate = null;
    if ($periode === 'month') {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
    } elseif ($periode === 'year') {
        $startDate = now()->month >= 9 ? now()->month(9)->startOfMonth() : now()->subYear()->month(9)->startOfMonth();
        $endDate = $startDate->copy()->addYear()->subDay();
    }

    // 2. Filtrage des Séances
    $seanceQuery = Seance::where('formateur_id', $formateurId);
    if ($startDate && $endDate) {
        $seanceQuery->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
    }
    if ($groupeId && $groupeId !== 'all') $seanceQuery->where('groupe_id', $groupeId);
    if ($moduleId && $moduleId !== 'all') $seanceQuery->where('module_id', $moduleId);

    $seanceIds = $seanceQuery->pluck('id');
    $groupeIds = $seanceQuery->pluck('groupe_id')->unique();
    $moduleIds = $seanceQuery->pluck('module_id')->unique();

    // 3. Calcul des Notes Globales
    $notesQuery = Note::whereIn('module_id', $moduleIds)
        ->whereIn('stagiaire_id', function($q) use ($groupeIds) {
            $q->select('id')->from('stagiaire_profiles')->whereIn('groupe_id', $groupeIds);
        });
    if ($startDate) $notesQuery->whereBetween('created_at', [$startDate, $endDate]);

    $notes = $notesQuery->get();

    // 4. Calcul des Statistiques par Stagiaire (Formule OFPPT)
    $allStudents = $this->getAllStudentsStats($groupeIds, $moduleIds, $startDate, $endDate);

    // 5. Taux de présence
    $totalAppels = Absence::whereIn('seance_id', $seanceIds)->count();
    $totalAbsences = Absence::whereIn('seance_id', $seanceIds)->where('est_en_retard', 0)->count();
    $tauxPresence = $totalAppels > 0 ? round(100 - (($totalAbsences / $totalAppels) * 100), 1) : 100;

    return response()->json([
        'globalStats' => [
            'totalEtudiants' => StagiaireProfile::whereIn('groupe_id', $groupeIds)->count(),
            'moyenneGénérale' => round($allStudents->avg('moyenne'), 2) ?: 0,
            'tauxPresence' => $tauxPresence,
            'tauxReussite' => $allStudents->count() > 0 ? round(($allStudents->where('moyenne', '>=', 10)->count() / $allStudents->count()) * 100, 1) : 0
        ],
        'allStudentsStats' => $allStudents,
        'moyennesData' => $this->getMoyennesEvolution($moduleIds, $groupeIds),
        'presenceData' => [['module' => 'Global', 'present' => $tauxPresence, 'absent' => round(100 - $tauxPresence, 1)]],
        'notesDistribution' => [
            ['range' => '0-10', 'count' => $allStudents->where('moyenne', '<', 10)->count()],
            ['range' => '10-14', 'count' => $allStudents->whereBetween('moyenne', [10, 14])->count()],
            ['range' => '14-20', 'count' => $allStudents->where('moyenne', '>=', 14)->count()],
        ],
        'mentionsData' => [
            ['name' => 'Excellent', 'value' => $allStudents->where('moyenne', '>=', 16)->count(), 'color' => '#00C9A7'],
            ['name' => 'Bien', 'value' => $allStudents->whereBetween('moyenne', [12, 16])->count(), 'color' => '#1E88E5'],
            ['name' => 'Passable', 'value' => $allStudents->whereBetween('moyenne', [10, 12])->count(), 'color' => '#FF9800'],
            ['name' => 'Insuffisant', 'value' => $allStudents->where('moyenne', '<', 10)->count(), 'color' => '#EF5350'],
        ],
        'topStudents' => $allStudents->sortByDesc('moyenne')->take(5)->values(),
        'strugglingStudents' => $allStudents->where('moyenne', '<', 10)->sortBy('moyenne')->take(5)->values(),
        'difficultModules' => [],
        'successModules' => [],
        'absencesTendances' => []
    ]);
}


private function getMoyennesEvolution($moduleIds, $groupeIds)
{
    // On vérifie que les variables sont bien passées en argument au début (ligne 1)
    return Note::whereIn('module_id', $moduleIds)
        ->whereIn('stagiaire_id', function($q) use ($groupeIds) {
            // Le "use ($groupeIds)" permet d'utiliser la variable à l'intérieur
            $q->select('id')->from('stagiaire_profiles')->whereIn('groupe_id', $groupeIds);
        })
        ->select(
            DB::raw('MONTH(created_at) as mois_num'),
            DB::raw('DATE_FORMAT(created_at, "%b") as mois'),
            DB::raw('AVG(valeur) as moyenne')
        )
        ->groupBy('mois_num', 'mois')
        ->orderBy('mois_num')
        ->get()
        ->map(function($item) {
            return [
                'mois' => $item->mois,
                'moyenne' => round($item->moyenne, 2)
            ];
        });
}

private function getAllStudentsStats($groupeIds, $moduleIds, $startDate, $endDate)
{
    return StagiaireProfile::whereIn('groupe_id', $groupeIds)
        ->get()
        ->map(function($s) use ($moduleIds, $startDate, $endDate) {
            // On récupère les notes CC et EFM
            $notes = Note::where('stagiaire_id', $s->id)
                ->whereIn('module_id', $moduleIds);

            if ($startDate && $endDate) {
                $notes->whereBetween('created_at', [$startDate, $endDate]);
            }

            $allNotes = $notes->get();

            // Calcul de la moyenne des CC (CC1, CC2, CC3)
            $ccNotes = $allNotes->whereIn('type_evaluation', ['cc1', 'cc2', 'cc3'])->pluck('valeur');
            $avgCC = $ccNotes->count() > 0 ? $ccNotes->avg() : null;

            // Note EFM (sur 40)
            $efmNote = $allNotes->where('type_evaluation', 'efm')->first()?->valeur;

            // Application de ta formule : ((CC1+CC2+CC3)/3 + EFM) / 3
            $moyenneFinale = 0;
            if ($avgCC !== null && $efmNote !== null) {
                $moyenneFinale = ($avgCC + $efmNote) / 3;
            } elseif ($avgCC !== null) {
                $moyenneFinale = $avgCC; // Si pas d'EFM, on garde la moyenne CC
            }

            return [
                'name' => $s->nom . ' ' . $s->prenom,
                'moyenne' => round($moyenneFinale, 2)
            ];
        })->values();
}
}
