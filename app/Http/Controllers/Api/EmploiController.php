<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emploi;
use App\Models\Groupe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmploiController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'titre'   => 'required|string',
            'fichier' => 'required|file|mimes:pdf,jpg,png,xlsx,xls|max:10240',
            'all_groups' => 'required',
        ]);

        try {
            // 2. Upload du fichier
            $path = $request->file('fichier')->store('emplois', 'public');

            // 3. Création de l'emploi
            $emploi = Emploi::UpdateOrCreate([
                'titre'     => $request->titre,
                'file_path' => $path,
            ]);

            // 4. LOGIQUE POUR RENDRE LISIBLE PAR TOUS LES GROUPES
            // On récupère TOUS les IDs de la table groupes
            // pluck('id') transforme la collection en un simple tableau de chiffres [1, 2, 3...]
            $allGroupIds = Groupe::pluck('id')->toArray();

            // 5. Liaison massive dans la table pivot
            // sync() va créer une ligne dans 'emploi_groupe' pour CHAQUE groupe existant
            $emploi->groupes()->sync($allGroupIds);

            return response()->json([
                'message' => 'L\'emploi du temps est maintenant visible par tous les groupes !',
                'count'   => count($allGroupIds)
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Côté Stagiaire : Récupère l'emploi lié à SON groupe
     */
public function getForStagiaire()
{
    $user = Auth::user();

    // 1. On va chercher le groupe_id dans la table stagiaireProfiles
    // Assure-toi que 'user_id' est bien le nom de la colonne qui relie au stagiaire
    $groupeId = DB::table('stagiaire_profiles')
                    ->where('user_id', $user->id)
                    ->value('groupe_id');

    // 2. Si on ne trouve aucun groupe pour cet utilisateur
    if (!$groupeId) {
        return response()->json([], 200);
    }

    // 3. On récupère les emplois liés à ce groupe spécifique
    $emplois = \App\Models\Emploi::whereHas('groupes', function($query) use ($groupeId) {
        // Attention : vérifie si dans ta table pivot la colonne s'appelle 'group_id' ou 'groupe_id'
        $query->where('emploi_groupe.group_id', $groupeId);
    })->latest()->take(1)->get();

    return response()->json($emplois);
}
}
