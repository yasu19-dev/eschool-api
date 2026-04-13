<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Seance;
use App\Models\StagiaireProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AbsenceExport;
use Maatwebsite\Excel\Facades\Excel;

class AbsenceController extends Controller
{
    /**
     * Pour le Directeur : Statistiques globales
     */
    // AbsenceController.php

// app/Http/Controllers/Api/AbsenceController.php

public function storeBulk(Request $request)
{
    $validated = $request->validate([
        'seance_id' => 'required|exists:seances,id',
        'date' => 'required|date', // On valide la date reçue
        'stagiaires' => 'required|array',
        'stagiaires.*.id' => 'required|exists:stagiaire_profiles,id',
        'stagiaires.*.est_en_retard' => 'required|boolean',
    ]);

    // 1. On efface TOUT pour cette séance et cette date avant de réécrire
    // Cela permet de gérer ceux qui redeviendraient "Présents"
    Absence::where('seance_id', $validated['seance_id'])
                        ->where('date', $validated['date'])
                        ->delete();

    foreach ($validated['stagiaires'] as $item) {
        Absence::updateOrCreate(
            [
                'seance_id' => $validated['seance_id'],
                'stagiaire_id' => $item['id'],
                'date' => $validated['date'] // <--- CRUCIAL : On cherche par date aussi !
            ],
            [
                'est_en_retard' => $item['est_en_retard'],
                'est_justifie' => false
            ]
        );
    }

    return response()->json(['message' => 'Appel enregistré !']);
}
    public function globalStats()
    {
        // Logique : Compter les absences réelles (est_en_retard = false)
        return response()->json([
            'total_absences' => Absence::where('est_en_retard', false)->count(),
            'total_retards' => Absence::where('est_en_retard', true)->count(),
        ]);
    }

    /**
     * Pour le Stagiaire : Liste de ses propres absences
     */
    public function myAbsences(Request $request)
    {
        $user = $request->user();
        return response()->json($user->stagiaireProfile->absences()->with('seance.module')->get());
    }

    // app/Http/Controllers/Api/AbsenceController.php

public function history(Request $request)
{
    $formateurId = $request->user()->formateurProfile->id;

    return Absence::whereHas('seance', function ($query) use ($formateurId) {
        $query->where('formateur_id', $formateurId);
    })
    ->with(['seance.groupe', 'seance.module'])
    ->select('seance_id', 'date')
    ->selectRaw('count(case when est_en_retard = 0 then 1 end) as absents_count')
    ->selectRaw('count(case when est_en_retard = 1 then 1 end) as retards_count')
    ->groupBy('seance_id', 'date')
    ->orderBy('date', 'desc')
    ->get();
}

// Nouvelle méthode pour charger les absences d'une séance précise lors d'une rectification
public function getAbsencesBySession(Request $request)
{
    // Debug : On force Laravel à nous dire ce qu'il reçoit
    // return response()->json($request->all());

    $validated = $request->validate([
        'seance_id' => 'required',
        'date'      => 'required|date',
    ]);

    $absences = Absence::where('seance_id', $validated['seance_id'])
        ->where('date', $validated['date'])
        ->get(['stagiaire_id', 'est_en_retard']);

    return response()->json($absences);
}

public function recent(Request $request)
{
    $formateur = $request->user()->formateurProfile;

    if (!$formateur) {
        return response()->json(['message' => 'Profil non trouvé'], 404);
    }

    // On cherche les absences qui appartiennent aux SÉANCES de ce formateur
    $absences = Absence::whereHas('seance', function($query) use ($formateur) {
            $query->where('formateur_id', $formateur->id);
        })
        ->with([
            'stagiaire:id,nom,prenom',
            'seance.groupe:id,code' // On récupère le groupe via la séance
        ])
        ->orderBy('created_at', 'desc') // On trie par date de création
        ->limit(5)
        ->get();

    return response()->json($absences);
}

// ?? la logique de statistiques


public function getAdminStats(Request $request)
{
    $filiereCode = $request->query('filiere', 'all');
    $period = $request->query('period', 'month');
    $groupId = $request->query('group_id', 'all');
    $view = $request->query('view', 'global');

    // Dates de début selon la période
    $startDate = Carbon::now();
    if ($period === 'week') $startDate = $startDate->startOfWeek();
    elseif ($period === 'year') $startDate = $startDate->startOfYear();
    elseif ($period === 'semester') $startDate = $startDate->subMonths(6);
    else $startDate = $startDate->startOfMonth();

    // Requête de base pour les absences filtrées
    $baseQuery = Absence::join('seances', 'absences.seance_id', '=', 'seances.id')
        ->join('stagiaire_profiles', 'absences.stagiaire_id', '=', 'stagiaire_profiles.id')
        ->join('groupes', 'stagiaire_profiles.groupe_id', '=', 'groupes.id')
        ->join('modules', 'seances.module_id', '=', 'modules.id')
        ->where('absences.date', '>=', $startDate->format('Y-m-d'));

    if ($groupId !== 'all') $baseQuery->where('groupes.id', $groupId);
    if ($filiereCode !== 'all') $baseQuery->where('groupes.code', 'like', $filiereCode . '%');

    // --- CALCULS DES CARTES ---
    $totalAbsences = (clone $baseQuery)->where('est_en_retard', false)->count();
    $totalRetards = (clone $baseQuery)->where('est_en_retard', true)->count();
    $studentsAbsentCount = (clone $baseQuery)->distinct('absences.stagiaire_id')->count('absences.stagiaire_id');
    $heuresPerdues = $totalAbsences * 2.5;

    $totalSessions = Seance::where('date', '>=', $startDate->format('Y-m-d'))->count();
    $totalStagiaires = StagiaireProfile::count();
    $divisor = ($totalSessions * $totalStagiaires) ?: 1;
    $tauxGlobal = ($totalAbsences / $divisor) * 100;
    $tauxParSeance = $totalSessions > 0 ? ($totalAbsences / $totalSessions) : 0;

    // --- GRAPHIQUES ---

    // A. Évolution
    $evolution = (clone $baseQuery)
        ->select(DB::raw('DATE_FORMAT(absences.date, "%d/%m") as date_label'), DB::raw('count(*) as count'))
        ->groupBy('date_label')
        ->orderBy(DB::raw('MIN(absences.date)'), 'asc')
        ->get()
        ->map(fn($item) => [
            'mois' => $item->date_label,
            'absences' => $item->count,
            'taux' => round(($item->count / $divisor) * 100, 1)
        ]);

    // B. Répartition Type
    $justified = (clone $baseQuery)->where('est_justifie', true)->count();
    $notJustified = $totalAbsences - $justified;

    // C. Tendances par Jour
    $absencesParJour = (clone $baseQuery)
        ->select(DB::raw('DAYNAME(absences.date) as jour_name'), DB::raw('count(*) as total'))
        ->groupBy('jour_name')
        ->orderBy(DB::raw('DAYOFWEEK(MIN(absences.date))'))
        ->get()
        ->map(fn($item) => ['jour' => substr($item->jour_name, 0, 3), 'absences' => $item->total]);

    // D. Tendances par Créneau
    $absencesParCreneau = (clone $baseQuery)
        ->select('seances.creneau', DB::raw('count(*) as total'))
        ->groupBy('seances.creneau')
        ->orderBy('seances.creneau')
        ->get()
        ->map(fn($item) => ['creneau' => $item->creneau, 'absences' => $item->total]);

    // E. Top Étudiants
    $topStudents = (clone $baseQuery)->where('est_en_retard', false)
        ->select('stagiaire_profiles.nom', 'stagiaire_profiles.prenom', 'groupes.code', DB::raw('count(*) as total'), DB::raw('SUM(est_justifie) as justifiees'))
        ->groupBy('stagiaire_profiles.id', 'stagiaire_profiles.nom', 'stagiaire_profiles.prenom', 'groupes.code')
        ->orderByDesc('total')->limit(5)->get()
        ->map(fn($s) => ['name' => $s->nom.' '.$s->prenom, 'groupe' => $s->code, 'absences' => $s->total, 'justifiees' => (int)$s->justifiees]);

    // F. Par Filière
    $absencesParFiliere = (clone $baseQuery)
        ->join('filieres', 'groupes.code', 'like', DB::raw("CONCAT(filieres.code, '%')"))
        ->select('filieres.code as filiere', DB::raw('count(*) as absences'))
        ->groupBy('filieres.code')
        ->get()
        ->map(fn($item) => [
            'filiere' => $item->filiere,
            'absences' => $item->absences,
            'taux' => round(($item->absences / $divisor) * 100, 1)
        ]);

    // --- MODIFICATION ICI : NOM COMPLET DU FORMATEUR ---
    $modulesAbsences = (clone $baseQuery)
        ->join('formateur_profiles', 'seances.formateur_id', '=', 'formateur_profiles.id')
        ->select(
            'modules.intitule',
            'formateur_profiles.nom',
            'formateur_profiles.prenom', // Ajout du prénom
            DB::raw('count(*) as total')
        )
        ->groupBy('modules.intitule', 'formateur_profiles.nom', 'formateur_profiles.prenom')
        ->orderByDesc('total')->limit(5)->get()
        ->map(function($m) {

            return [
                'module' => $m->intitule,
                'formateur' =>   strtoupper($m->nom).' '.strtoupper($m->prenom),
                'absences' => $m->total
            ];
        });

    return response()->json([
        'cards' => [
            ['title' => "Taux d'absence global", 'value' => number_format($tauxGlobal, 1) . '%', 'icon' => 'AlertCircle', 'color' => '#EF5350'],
            ['title' => 'Taux par séance (Moy.)', 'value' => number_format($tauxParSeance, 1) . '%', 'icon' => 'Clock', 'color' => '#1E88E5'],
            ['title' => 'Retards Signalés', 'value' => $totalRetards, 'icon' => 'Users', 'color' => '#FF9800'],
            ['title' => 'Absences sur la période', 'value' => $totalAbsences, 'icon' => 'Calendar', 'color' => '#FF9800'],
            ['title' => 'Étudiants absents', 'value' => $studentsAbsentCount, 'icon' => 'Users', 'color' => '#1E88E5'],
            ['title' => 'Heures perdues', 'value' => number_format($heuresPerdues, 1, '.', ' ') . 'h', 'icon' => 'Clock', 'color' => '#9C27B0'],
        ],
        'evolution' => $evolution,
        'types' => [
            ['name' => 'Justifiées', 'value' => $justified, 'color' => '#00C9A7'],
            ['name' => 'Non justifiées', 'value' => $notJustified, 'color' => '#EF5350'],
        ],
        'absencesParJour' => $absencesParJour,
        'absencesParCreneau' => $absencesParCreneau,
        'topStudents' => $topStudents,
        'modulesAbsences' => $modulesAbsences,
        'absencesParFiliere' => $absencesParFiliere,
    ]);
}
// ?? exporter les données pour Excel



public function exportAbsences(Request $request)
    {
        $filiereCode = $request->query('filiere', 'all');
        $period = $request->query('period', 'month');
        $groupId = $request->query('group_id', 'all');

        // 1. Définition de la période
        $startDate = Carbon::now();
        if ($period === 'week') $startDate = $startDate->startOfWeek();
        elseif ($period === 'year') $startDate = $startDate->startOfYear();
        elseif ($period === 'semester') $startDate = $startDate->subMonths(6);
        else $startDate = $startDate->startOfMonth();

        // 2. Requête détaillée avec les alias correspondant à votre classe Export
        $query = Absence::join('seances', 'absences.seance_id', '=', 'seances.id')
            ->join('stagiaire_profiles', 'absences.stagiaire_id', '=', 'stagiaire_profiles.id')
            ->join('groupes', 'stagiaire_profiles.groupe_id', '=', 'groupes.id')
            ->join('modules', 'seances.module_id', '=', 'modules.id')
            ->join('formateur_profiles', 'seances.formateur_id', '=', 'formateur_profiles.id')
            ->where('absences.date', '>=', $startDate->format('Y-m-d'));

        // 3. Application des filtres dynamiques
        if ($groupId !== 'all') {
            $query->where('groupes.id', $groupId);
        }

        if ($filiereCode !== 'all') {
            $query->where('groupes.code', 'like', $filiereCode . '%');
        }

        // 4. Sélection avec les alias utilisés dans AbsenceExport.php
        $data = $query->select(
            'absences.date',
            'stagiaire_profiles.cef',
            'stagiaire_profiles.nom as s_nom',
            'stagiaire_profiles.prenom as s_prenom',
            'groupes.code as groupe',
            'modules.intitule as module',
            'formateur_profiles.nom as f_nom',
            'formateur_profiles.prenom as f_prenom',
            'absences.est_en_retard',
            'absences.est_justifie',
            'absences.motif'
        )
        ->orderBy('absences.date', 'desc')
        ->get();

        // 5. Lancement du téléchargement Excel
        return Excel::download(new AbsenceExport($data), 'Rapport_Absences_ISMONTIC.xlsx');
    }






// ?? logique de generer rapport
public function generatePdfReport(Request $request)
{
    // On récupère exactement les mêmes données que ton tableau de bord
    $response = $this->getAdminStats($request);
    $data = $response->getData(true);

    // On prépare les variables pour la vue
    $pdfData = [
        'cards' => $data['cards'],
        'topStudents' => $data['topStudents'],
        'filiere' => $request->query('filiere', 'Toutes'),
        'period' => $request->query('period', 'Mois en cours'),
    ];

    // Génération du PDF
    $pdf = Pdf::loadView('pdf.absences_report', $pdfData);

    // On force le format A4 Portrait
    $pdf->setPaper('a4', 'portrait');

    return $pdf->download('Rapport_Absences_ISMONTIC.pdf');
}

}
