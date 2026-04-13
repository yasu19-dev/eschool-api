<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Stagiaire\StagiaireProfileResource;
use App\Models\Reclamation;
use App\Models\DemandeAttestation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StagiaireController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    // Route: GET /stagiaire/dashboard
    // Route: GET /stagiaire/dashboard
    public function index(Request $request)
    {
        $profile = $request->user()->stagiaireProfile;

        if (!$profile || !$profile->groupe_id) {
            return response()->json(['message' => 'Aucun groupe assigné'], 404);
        }

        $groupeId = $profile->groupe_id;

        // ==========================================
        // 1. STATISTIQUES (Moyenne, Présence, Docs, Annonces)
        // ==========================================

        // Calcul de la moyenne
        $moyenne = \App\Models\Note::where('stagiaire_id', $profile->id)->avg('valeur');
        $moyenneFormat = $moyenne ? number_format($moyenne, 2) . '/20' : '--/20';

        // Calcul du taux de présence
        $totalSeances = \App\Models\Seance::where('groupe_id', $groupeId)->where('date', '<=', now())->count();
        $absences = $profile->absences()->count(); // Utilisation de ta relation existante
        $tauxPresence = $totalSeances > 0 ? round((($totalSeances - $absences) / $totalSeances) * 100) . '%' : '--%';

        // Comptage des documents et annonces
        $annoncesCount = \App\Models\Annonce::where('groupe_id', $groupeId)->where('created_at', '>=', now()->subDays(7))->count();


        // ==========================================
        // 2. LE PROCHAIN COURS (Upcoming Course)
        // ==========================================
        $nextSeance = \App\Models\Seance::with(['module', 'formateur'])
            ->where('groupe_id', $groupeId)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->first();

        $upcomingCourse = null;
        if ($nextSeance) {
            $nomFormateur = $nextSeance->formateur ? $nextSeance->formateur->nom . ' ' . $nextSeance->formateur->prenom : 'À définir';

            $upcomingCourse = [
                'module' => $nextSeance->module ? $nextSeance->module->intitule : 'Module Inconnu',
                'time' => $nextSeance->creneau ?? '--:--',
                'room' => $nextSeance->salle ?? 'À définir',
                'formateur' => 'Prof. ' . $nomFormateur
            ];
        }


        // ==========================================
        // 3. LES DERNIÈRES NOTES
        // ==========================================
        $recentNotes = \App\Models\Note::with('module')
            ->where('stagiaire_id', $profile->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($note) {
                return [
                    'module' => $note->module ? $note->module->intitule : 'Matière inconnue',
                    'note' => $note->valeur,
                    'type' => $note->type ?? 'Évaluation'
                ];
            });


        // ==========================================
        // 4. LES DERNIÈRES ANNONCES
        // ==========================================
        $recentAnnouncements = \App\Models\Annonce::where('groupe_id', $groupeId)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($annonce) {
                return [
                    'title' => $annonce->titre,
                    'date' => $annonce->created_at->diffForHumans(), // Format "Il y a 2 heures"
                    'category' => $annonce->categorie ?? 'Information'
                ];
            });


        // ==========================================
        // RETOUR JSON FINAL POUR REACT
        // ==========================================
        return response()->json([
            'stats' => [
                'moyenne' => $moyenneFormat,
                'taux_presence' => $tauxPresence,
                'annonces' => $annoncesCount . ' nouvelles'
            ],
            'upcomingCourse' => $upcomingCourse,
            'recentNotes' => $recentNotes,
            'recentAnnouncements' => $recentAnnouncements
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
// ==========================================
    // 1. RÉCUPÉRER L'HISTORIQUE DES RÉCLAMATIONS
    // Route: GET /api/stagiaire/reclamations
    // ==========================================
    public function getReclamations(Request $request)
    {
        $profile = $request->user()->stagiaireProfile;

        if (!$profile) {
            return response()->json(['message' => 'Profil stagiaire introuvable.'], 404);
        }

        // On récupère toutes les réclamations, de la plus récente à la plus ancienne
        $reclamations = \App\Models\Reclamation::where('stagiaire_id', $profile->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reclamations);
    }

    // ==========================================
    // 2. SOUMETTRE UNE NOUVELLE RÉCLAMATION
    // Route: POST /api/stagiaire/reclamations
    // ==========================================
    public function postReclamation(Request $request)
    {
        // 1. Validation des données envoyées par React
        $data = $request->validate([
            'type' => 'required|string',
            'message' => 'required|string',
        ]);

        $profile = $request->user()->stagiaireProfile;

        if (!$profile) {
            return response()->json(['message' => 'Profil stagiaire introuvable.'], 404);
        }

        // 2. Ajout de l'ID du stagiaire
        $data['stagiaire_id'] = $profile->id;

        // 3. Forcer le statut initial (si ce n'est pas déjà géré automatiquement par ta base de données)
        // Vérifie si ta colonne s'appelle "status" ou "statut" dans ta migration !
        $data['status'] = 'En attente';

        // 4. Création dans la base de données
        $reclamation = \App\Models\Reclamation::create($data);

        return response()->json([
            'message' => 'Réclamation enregistrée avec succès',
            'data' => $reclamation
        ], 201);
    }
    // 4. Demander une attestation
    // Route: POST /api/stagiaire/attestations
// ==========================================
    // 1. RÉCUPÉRER L'HISTORIQUE DES ATTESTATIONS
    // Route: GET /api/stagiaire/attestations
    // ==========================================
    public function getAttestations(Request $request)
    {
        $profile = $request->user()->stagiaireProfile;

        $attestations = \App\Models\DemandeAttestation::where('stagiaire_id', $profile->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($attestations);
    }

    // ==========================================
    // 2. SOUMETTRE UNE DEMANDE D'ATTESTATION
    // Route: POST /api/stagiaire/attestations
    // ==========================================
    public function postAttestation(Request $request)
{
    $profile = $request->user()->stagiaireProfile;

    // 1. Validation du type
    $request->validate([
        'type' => 'required|string|in:Attestation de scolarité,Attestation de stage,Relevé de notes,Retrait de Bac provisoire,Retrait de Bac définitif',
    ]);

    $typeDemande = $request->type;

    // 2. Vérification : Est-ce que ce TYPE a déjà été demandé ce mois-ci ?
    $currentMonth = now()->month;
    $currentYear = now()->year;

    $existingRequest = \App\Models\DemandeAttestation::where('stagiaire_id', $profile->id)
        ->where('type', $typeDemande) // On filtre par le type spécifique
        ->whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear)
        ->exists();

    if ($existingRequest) {
        return response()->json([
            'message' => "Vous avez déjà demandé une \"$typeDemande\" ce mois-ci. Vous pourrez en refaire une le mois prochain."
        ], 403);
    }

    // 3. Création
    $demande = \App\Models\DemandeAttestation::create([
        'stagiaire_id' => $profile->id,
        'type' => $typeDemande,
        'status' => 'En attente'
    ]);

    return response()->json([
        'message' => 'Demande enregistrée avec succès.',
        'data' => $demande
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
// public function getEmploi(Request $request)
// {
//     // On récupère le groupe du stagiaire connecté
//     $groupeId = $request->user()->stagiaireProfile->groupe_id;

//     $emploi = \App\Models\EmploiDuTempsPdf::where('groupe_id', $groupeId)
//                 ->latest() // On prend le dernier ajouté
//                 ->first();

//     if (!$emploi) {
//         return response()->json(['message' => 'Aucun emploi du temps disponible'], 404);
//     }

//     return response()->json([
//         'titre' => $emploi->titre,
//         'url' => $emploi->full_url, // URL générée par l'accesseur
//         'format' => $emploi->format,
//         'date' => $emploi->created_at->format('d/m/Y')
//     ]);
// }

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
