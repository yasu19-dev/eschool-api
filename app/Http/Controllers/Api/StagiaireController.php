<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use App\Models\DemandeAttestation;
use Illuminate\Http\Request;

class StagiaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Route: GET /stagiaire/dashboard
    public function index(Request $request)
    {
        $profile = $request->user()->stagiaireProfile;
        return response()->json([
            'stats' => [
                'absences' => $profile->absences()->count(),
                'retards' => $profile->absences()->where('est_en_retard', true)->count(),
                'notes_count' => $profile->notes()->count(),
            ],
            'recent_annonces' => \App\Models\Annonce::where('groupe_id', $profile->groupe_id)->latest()->take(3)->get()
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

    // 1. Récupérer les notes du stagiaire connecté
    public function getNotes(Request $request)
    {
        $profile = $request->user()->stagiaireProfile;
        // On charge les notes avec les informations du module associé
        return response()->json($profile->notes()->with('module')->get());
    }


    public function getAbsences(Request $request) {
        return response()->json($request->user()->stagiaireProfile->absences()->with('seance.module')->get());
        }

        // 2. Récupérer l'emploi du temps du groupe
        // Route: GET /stagiaire/schedule
// app/Http/Controllers/Api/StagiaireController.php

public function getSchedule(Request $request)
{
    $profile = $request->user()->stagiaireProfile;

    if (!$profile || !$profile->groupe_id) {
        return response()->json(['message' => 'Vous n\'êtes affecté à aucun groupe.'], 404);
    }

    // CORRECTION ICI : On enlève latest() car tes created_at sont NULL
    $schedule = \App\Models\EmploiDuTempsPdf::where('groupe_id', $profile->groupe_id)
                ->first(); // On prend juste le premier qu'on trouve

    if (!$schedule) {
        return response()->json([
            'message' => "Aucun emploi du temps n'a encore été publié pour votre groupe.",
            'groupe_id' => $profile->groupe_id
        ], 404);
    }

    return response()->json([
        'titre' => $schedule->titre,
        'fichier_url' => $schedule->fichier_url,
        'format' => $schedule->format,
        // On vérifie si created_at existe avant de formater
        'date_publication' => $schedule->created_at ? $schedule->created_at->format('d/m/Y') : 'Non spécifiée'
    ]);
}
    // 3. Poster une réclamation
  // Route: POST /stagiaire/reclamations
public function postReclamation(Request $request)
{
    // 1. On valide uniquement ce qui existe dans ta table
    $data = $request->validate([
        'type' => 'required|string', // Ex: Réclamation pédagogique
        'message' => 'required|string',
    ]);

    // 2. On récupère l'ID du profil stagiaire connecté
    $data['stagiaire_id'] = $request->user()->stagiaireProfile->id;

    // 3. Le statut sera "En cours" par défaut (défini dans ta migration)
    $reclamation = Reclamation::create($data);

    return response()->json([
        'message' => 'Réclamation enregistrée avec succès',
        'data' => $reclamation
    ], 201);
}
    // 4. Demander une attestation
    // Route: POST /api/stagiaire/attestations
public function postAttestation(Request $request)
{
    // 1. Validation rigoureuse selon tes commentaires de migration
    $request->validate([
        'type' => 'required|string|in:Scolarité,Récupération Bac provisoire,Récupération Bac définitive',
    ]);

    $profile = $request->user()->stagiaireProfile;

    // 2. Création de la demande
    // Le 'status' sera "En attente" par défaut grâce à ta migration
    $demande = \App\Models\DemandeAttestation::create([
        'stagiaire_id' => $profile->id,
        'type' => $request->type,
    ]);

    return response()->json([
        'message' => 'Demande d\'attestation enregistrée avec succès.',
        'reference' => $demande->id, // L'ID servira de base au format ATT-2025-XXX
        'status' => $demande->status
    ], 201);
}
}
