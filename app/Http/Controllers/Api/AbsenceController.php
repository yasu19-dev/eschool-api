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
}
