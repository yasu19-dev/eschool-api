<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    /**
     * Pour le Directeur : Statistiques globales
     */
    // AbsenceController.php

public function store(Request $request)
{
    // 1. Validation des données
    $validated = $request->validate([
        'seance_id' => 'required|exists:seances,id',
        'stagiaires' => 'required|array',
        'stagiaires.*.id' => 'required|exists:stagiaire_profiles,id',
        'stagiaires.*.est_en_retard' => 'required|boolean',
    ]);

    $formateurId = $request->user()->formateurProfile->id;

    // 2. Vérification de sécurité : La séance appartient-elle bien au formateur ?
    $seance = \App\Models\Seance::where('id', $validated['seance_id'])
                                ->where('formateur_id', $formateurId)
                                ->firstOrFail();

    // 3. Enregistrement massif
    foreach ($validated['stagiaires'] as $item) {
        Absence::updateOrCreate(
            ['seance_id' => $seance->id, 'stagiaire_id' => $item['id']],
            ['est_en_retard' => $item['est_en_retard'], 'est_justifie' => false]
        );
    }

    return response()->json(['message' => 'Appel terminé et enregistré !']);
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
}
