<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\User;
use App\Models\Groupe; // AJOUTÉ : Import du modèle Groupe
use App\Models\StagiaireProfile;
use App\Models\FormateurProfile;
use Illuminate\Http\Request;

class DirectorController extends Controller
{
    /**
     * Résumé pour le Dashboard Directeur (Stats en haut de page)
     */
    public function index()
    {
        return response()->json([
            'total_stagiaires' => StagiaireProfile::count(),
            'total_formateurs' => FormateurProfile::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'taux_presence_global' => '92%',
            'alertes_absences' => Absence::where('est_en_retard', false)->count()
        ]);
    }

    /**
     * Liste tous les utilisateurs avec leurs profils pour le tableau React.
     * Note : 'lastLogin' est automatiquement inclus car c'est un champ de la table users.
     */
    public function getUsers() {
        return User::with(['adminProfile', 'formateurProfile', 'stagiaireProfile'])
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Récupère la liste des groupes pour le <Select> du formulaire d'ajout.
     * On ne prend que l'ID et le NOM pour la performance.
     */
    public function getGroupes()
    {
        // Récupère tous les groupes de la base de données
        return Groupe::select('id', 'code')->orderBy('code', 'asc')->get();
    }

    public function getSpecialites()
{
    // On renvoie une liste "en dur" mais propre pour le Select React
    return response()->json([
        ['id' => 'DD', 'nom' => 'Développement Digital'],
        ['id' => 'ID', 'nom' => 'Infrastructure Digitale'],
        ['id' => 'CS', 'nom' => 'Cybersécurité'],
        ['id' => 'IA', 'nom' => 'Intelligence Artificielle'],
    ]);
}

    /**
     * Supprimer un utilisateur.
     * Le "Cascade Delete" configuré dans le modèle User s'occupe des profils.
     */
    public function deleteUser(User $user) {
        $user->delete();
        return response()->json(['message' => 'Utilisateur et données liées supprimés avec succès']);
    }

    public function getDeletedUsers() {
    // On récupère uniquement les comptes qui ont un 'deleted_at'
    return User::onlyTrashed()->with(['adminProfile', 'formateurProfile', 'stagiaireProfile'])->get();
}

    /**
     * Statistiques pour les graphiques d'absences
     */
    public function getAbsenceStats()
    {
        return response()->json([
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr'],
            'data' => [65, 59, 80, 81]
        ]);
    }

    // --- Les méthodes standards de l'API (store, show, etc.) ---

    public function store(Request $request) { /* Géré par UserController ou ici si tu préfères */ }

    public function show(string $id) { }

    public function update(Request $request, string $id) { }

    public function destroy(string $id) { }
}
