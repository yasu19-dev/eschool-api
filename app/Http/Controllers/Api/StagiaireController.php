<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Stagiaire\StagiaireProfileResource;
use App\Models\Reclamation;
use App\Models\DemandeAttestation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
    public function show(Request $request)
    {
        // 1. Récupérer le profil lié à l'utilisateur authentifié
        $profile = $request->user()->stagiaireProfile;

        if (!$profile) {
            return response()->json(['message' => 'Profil stagiaire introuvable.'], 404);
        }

        // 2. Retourner les données formatées via la JsonResource
        // Note : On charge la relation 'groupe' pour éviter les erreurs dans la ressource
        return new StagiaireProfileResource($profile->load('groupe'));
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request)
    {
        $user = $request->user();
        $profile = $user->stagiaireProfile;

        // 1. Validation des données
        $validated = $request->validate([
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'telephone' => 'sometimes|nullable|string|max:20',
            'adresse' => 'sometimes|nullable|string|max:255',
            // Validation optionnelle pour le changement de mot de passe
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // 2. Mise à jour de l'email dans la table 'users'
        if ($request->has('email')) {
            $user->update(['email' => $validated['email']]);
        }

        // 3. Mise à jour des infos dans la table 'stagiaire_profiles'
        $profile->update($request->only(['telephone', 'adresse']));

        // 4. Gestion du changement de mot de passe si demandé
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Le mot de passe actuel est incorrect.'
                ], 422);
            }
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);
        }

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'data' => new StagiaireProfileResource($profile->load('groupe'))
        ]);
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
        return response()->json($profile->notes()
        ->with('module')
        ->get());
    }


    public function getAbsences(Request $request) {
        return response()->json($request->user()->stagiaireProfile->absences()->with('seance.module')->get());
        }

    // StagiaireController.php

    public function getByGroupe($groupe_id)
    {
        // On récupère tous les profils stagiaires liés à cet ID de groupe
        $stagiaires = \App\Models\StagiaireProfile::where('groupe_id', $groupe_id)->get();

        if ($stagiaires->isEmpty()) {
            return response()->json(['message' => 'Aucun stagiaire trouvé pour ce groupe'], 404);
        }

        return response()->json($stagiaires);
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
    $demande = DemandeAttestation::create([
        'stagiaire_id' => $profile->id,
        'type' => $request->type,
    ]);

    return response()->json([
        'message' => 'Demande d\'attestation enregistrée avec succès.',
        'reference' => $demande->id, // L'ID servira de base au format ATT-2025-XXX
        'status' => $demande->status
    ], 201);
}
// app/Http/Controllers/Api/StagiaireController.php

public function getModules(Request $request) {
    // On récupère directement les modules du groupe du stagiaire connecté
    $modules = $request->user()->stagiaireProfile->groupe->seances()
                ->with('module') // On charge la relation module pour éviter les N+1
                ->get()
                ->pluck('module') // On ne garde que les modules
                ->unique('id') // On s'assure d'avoir des modules uniques
                ->values(); // On réindexe la collection

    return response()->json($modules);
}
public function getEmploi(Request $request)
{
    // On récupère le groupe du stagiaire connecté
    $groupeId = $request->user()->stagiaireProfile->groupe_id;

    $emploi = \App\Models\EmploiDuTempsPdf::where('groupe_id', $groupeId)
                ->latest() // On prend le dernier ajouté
                ->first();

    if (!$emploi) {
        return response()->json(['message' => 'Aucun emploi du temps disponible'], 404);
    }

    return response()->json([
        'titre' => $emploi->titre,
        'url' => $emploi->full_url, // URL générée par l'accesseur
        'format' => $emploi->format,
        'date' => $emploi->created_at->format('d/m/Y')
    ]);
}

public function uploadPhoto(Request $request)
{
    $request->validate(['photo' => 'required|image|max:2048']);

    $profile = $request->user()->stagiaireProfile; // On cible le profil stagiaire

    if ($request->hasFile('photo')) {
        $path = $request->file('photo')->store('profiles/stagiaires', 'public');
        $profile->update(['photo_url' => asset('storage/' . $path)]);

        return response()->json(['photo_url' => asset('storage/' . $path)]);
    }
}
}
