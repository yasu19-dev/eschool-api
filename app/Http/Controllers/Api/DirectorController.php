<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\User;
use Illuminate\Http\Request;

class DirectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // DirectorController.php

public function index()
{
    // Résumé pour le Dashboard Directeur
    return response()->json([
        'total_stagiaires' => \App\Models\StagiaireProfile::count(),
        'total_formateurs' => \App\Models\FormateurProfile::count(),
        'taux_presence_global' => '92%', // À calculer via une requête complexe plus tard
        'alertes_absences' => Absence::where('est_en_retard', false)->count()
    ]);
}
public function getStats() {
        return response()->json([
            'total_absences' => Absence::where('est_en_retard', false)->count(),
            'taux_global' => '8.5%'
        ]);
    }

    public function getUsers() {
        return User::with(['adminProfile', 'formateurProfile', 'stagiaireProfile'])->get();
    }

    public function deleteUser($id)
{
    try {
        // 1. On récupère l'utilisateur (même avec UUID ça marche)
        $user = \App\Models\User::findOrFail($id);

        // 2. On lance la suppression "douce"
        // Laravel va remplir 'deleted_at' au lieu d'effacer la ligne
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur désactivé (Soft Delete) avec succès.',
            'deleted_at' => $user->deleted_at
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
        ], 500);
    }
}
// restaurer
public function restoreUser($id)
{
    // On utilise withTrashed() car sinon Laravel ne trouverait pas l'user supprimé
    $user = User::withTrashed()->findOrFail($id);

    $user->restore(); // Vide la colonne deleted_at

    return response()->json(['message' => 'Utilisateur restauré !']);
}

// Pour la page Statistiques d'absences
public function getAbsenceStats()
{
    // Récupérer les absences groupées par mois ou par filière
    return response()->json([
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr'],
        'data' => [65, 59, 80, 81]
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
}
