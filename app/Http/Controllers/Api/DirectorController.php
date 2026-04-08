<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\User;
use App\Models\Groupe;
use App\Models\StagiaireProfile;
use App\Models\FormateurProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DirectorController extends Controller
{
    // --- PARTIE DASHBOARD ---
    public function index() {
        return response()->json([
            'total_stagiaires' => StagiaireProfile::count(),
            'total_formateurs' => FormateurProfile::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'taux_presence_global' => '92%',
            'alertes_absences' => Absence::where('est_en_retard', false)->count()
        ]);
    }

    // --- PARTIE GESTION UTILISATEURS ---

    // Récupérer les membres actifs
    public function getUsers() {
        return User::with(['adminProfile', 'formateurProfile', 'stagiaireProfile'])
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    // Récupérer les membres archivés (LA CORRECTION POUR TON ERREUR 500)
    public function trashed() {
        return User::onlyTrashed()
                    ->with(['adminProfile', 'formateurProfile', 'stagiaireProfile'])
                    ->get();
    }

    // Création complète (Transféré depuis UserController pour tout centraliser)
    public function store(Request $request) {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,formateur,stagiaire',
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'cef' => 'required_if:role,stagiaire|nullable|string|unique:stagiaire_profiles,cef',
            'matricule' => 'required_if:role,formateur|nullable|string|unique:formateur_profiles,matricule',
            'groupe_id' => 'required_if:role,stagiaire|nullable|exists:groupes,id',
            'specialite' => 'required_if:role,formateur|nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'etat' => 'Actif',
            ]);

            if ($request->role === 'stagiaire') {
                $user->stagiaireProfile()->create([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'cef' => $request->cef,
                    'groupe_id' => $request->groupe_id,
                ]);
            } elseif ($request->role === 'formateur') {
                $user->formateurProfile()->create([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'specialite' => $request->specialite,
                    'matricule' => $request->matricule,
                    'email_professionnel' => $request->email, // ✅ Correction SQL effectuée
                ]);
            }

            return response()->json(['message' => 'Succès', 'user' => $user->load($request->role . 'Profile')], 201);
        });
    }

    // Archiver (Soft Delete)
    public function deleteUser(User $user) {
        $user->delete();
        return response()->json(['message' => 'Utilisateur archivé']);
    }

    // Restaurer
    public function restore($id) {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        return response()->json(['message' => 'Utilisateur restauré', 'user' => $user->load('stagiaireProfile', 'formateurProfile')]);
    }

    // --- PARTIE DONNÉES FORMULAIRES ---
    public function getGroupes() {
        return Groupe::select('id', 'code')->orderBy('code', 'asc')->get();
    }

    public function getSpecialites() {
        return response()->json([
            ['id' => 'DD', 'nom' => 'Développement Digital'],
            ['id' => 'ID', 'nom' => 'Infrastructure Digitale'],
            ['id' => 'CS', 'nom' => 'Cybersécurité'],
            ['id' => 'IA', 'nom' => 'Intelligence Artificielle'],
            ['id' => 'FR', 'nom' => 'Français'],
            ['id' => 'CN', 'nom' => 'Culture Numérique'],
            ['id' => 'EN', 'nom' => 'Anglais Technique'],
            ['id' => 'BDD', 'nom' => 'Base de Données'],
            ['id' => 'ALGO', 'nom' => 'Algorithmique'],


        ]);
    }
}
