<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emploi;
use App\Models\Groupe;
use Illuminate\Http\Request;

class GroupeController extends Controller
{
    /**
     * Récupère la liste de tous les groupes.
     * C'est cette méthode que ton React appelle via axios.get('/api/groupes')
     */
    public function index()
    {
        try {
            $groupes = Groupe::all(['id', 'nom']); // On ne prend que l'ID et le Nom
            return response()->json($groupes, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des groupes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optionnel : Créer un nouveau groupe (si tu en as besoin pour ton PFE)
     */
    public function store(Request $request)
{
    // 1. Validation souple pour all_groups
    $request->validate([
        'titre' => 'required|string',
        'fichier' => 'required|file|mimes:pdf,jpg,png,xlsx,xls|max:10240',
        'all_groups' => 'required',
    ]);

    // 2. Sauvegarde du fichier
    $path = $request->file('fichier')->store('emplois', 'public');

    // 3. Création de l'emploi
    $emploi = Emploi::create([
        'titre' => $request->titre,
        'file_path' => $path
    ]);

    // --- CORRECTION TECHNIQUE ICI ---

    // On convertit la chaîne "true"/"false" en vrai booléen PHP
    $isAllGroups = filter_var($request->all_groups, FILTER_VALIDATE_BOOLEAN);

    if ($isAllGroups) {
        // On récupère uniquement les IDs (entiers)
        $groupIds = \App\Models\Groupe::pluck('id')->toArray();
    } else {
        // On s'assure que ce sont des entiers si ça vient de group_ids
        $groupIds = array_map('intval', $request->group_ids ?? []);
    }

    // On attache les IDs propres à la table pivot
    $emploi->groupes()->sync($groupIds);

    return response()->json(['message' => 'Succès !'], 201);
}
}
