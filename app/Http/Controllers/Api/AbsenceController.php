<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use Illuminate\Http\Request;
// ? importation de stagiaireProfile
use App\Models\StagiaireProfile;
use Illuminate\Support\Facades\DB;

class AbsenceController extends Controller
{
    /**
     * Pour le Directeur : Statistiques globales
     */
    // AbsenceController.php

// app/Http/Controllers/Api/AbsenceController.php
 /**
     * ? NOUVELLE MÉTHODE : Statistiques pour le Tableau de Bord Admin
     * ? Cette méthode calcule les taux, les heures perdues (2.5h) et prépare les données pour Recharts.
     */


public function getAdminStats(Request $request)
{
    try {
        $filiere = $request->query('filiere', 'all');
        $period = $request->query('period', 'month');

        // Requête de base avec jointures
        $query = Absence::join('seances', 'absences.seance_id', '=', 'seances.id')
            ->join('stagiaire_profiles', 'absences.stagiaire_id', '=', 'stagiaire_profiles.id')
            ->join('groupes', 'stagiaire_profiles.groupe_id', '=', 'groupes.id');

        if ($filiere !== 'all') {
            $query->where('groupes.code', 'like', $filiere . '%');
        }

        if ($period === 'month') {
            $query->whereMonth('absences.date', now()->month);
        }

        // 1. CARDS (Calcul basé sur 2.5h)
        $absentsCount = (clone $query)->where('absences.est_en_retard', false)->count();
        $retardsCount = (clone $query)->where('absences.est_en_retard', true)->count();
        $uniqueStagiaires = (clone $query)->where('absences.est_en_retard', false)->distinct('stagiaire_id')->count();

        // 2. ÉVOLUTION (LineChart)
        $evolution = Absence::selectRaw("DATE_FORMAT(date, '%b') as mois, count(*) as absences")
            ->where('est_en_retard', false)
            ->groupBy('mois')->orderBy('date')->get();

        // 3. TYPES (PieChart)
        $types = [
            ['name' => 'Non justifiées', 'value' => (clone $query)->where('est_justifie', false)->count(), 'color' => '#EF5350'],
            ['name' => 'Justifiées', 'value' => (clone $query)->where('est_justifie', true)->count(), 'color' => '#00C9A7'],
        ];

        // 4. TOP STAGIAIRES (Sécurisé)
        $topStudents = StagiaireProfile::with('groupe')
            ->withCount(['absences' => fn($q) => $q->where('est_en_retard', false)])
            ->orderBy('absences_count', 'desc')->take(5)->get()
            ->map(function($s) {
                return [
                    'name' => $s->nom . ' ' . $s->prenom,
                    'groupe' => $s->groupe->code ?? 'N/A', // Sécurité si groupe absent
                    'absences' => $s->absences_count,
                    'justifiees' => $s->absences()->where('est_justifie', true)->count()
                ];
            });

        return response()->json([
            'globalStats' => [
                ['title' => 'Taux d\'absence global', 'value' => $absentsCount > 0 ? 'Réel' : '0%', 'color' => '#EF5350'],
                ['title' => 'Retards enregistrés', 'value' => $retardsCount, 'color' => '#1E88E5'],
                ['title' => 'Absences ce mois', 'value' => $absentsCount, 'color' => '#FF9800'],
                ['title' => 'Étudiants absents', 'value' => $uniqueStagiaires, 'color' => '#1E88E5'],
                ['title' => 'Heures perdues', 'value' => ($absentsCount * 2.5) . 'h', 'color' => '#9C27B0'],
            ],
            'evolution' => $evolution,
            'types' => $types,
            'topStudents' => $topStudents
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}








//  ! la logique de yasmine

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
}
