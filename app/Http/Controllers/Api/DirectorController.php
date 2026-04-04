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

    public function deleteUser(User $user) {
        $user->delete();
        return response()->json(['message' => 'Supprimé']);
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
