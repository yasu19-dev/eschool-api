<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs avec leurs profils respectifs.
     */
    public function index() {
        return User::with(['adminProfile', 'formateurProfile', 'stagiaireProfile'])->get();
        // SI TU VEUX TOUT (y compris les supprimés) pour ton tableau :
    // return User::withTrashed()->with(['adminProfile', 'formateurProfile', 'stagiaireProfile'])->get();
    }

    /**
     * Création d'un utilisateur et de son profil associé.
     */
    public function store(Request $request)
    {
        // 1. Validation intelligente des données
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,formateur,stagiaire',
            'nom' => 'required|string',
            'prenom' => 'required|string',

            // MODIF : CEF obligatoire UNIQUEMENT pour les stagiaires
            'cef' => 'required_if:role,stagiaire|nullable|string|unique:stagiaire_profiles,cef',
            'matricule' => 'required_if:role,formateur|nullable|string|unique:formateur_profiles,matricule', // 👈 AJOUTÉ

            // MODIF : Groupe obligatoire UNIQUEMENT pour les stagiaires
            'groupe_id' => 'required_if:role,stagiaire|nullable|exists:groupes,id',

            // MODIF : Spécialité obligatoire UNIQUEMENT pour les formateurs
            'specialite' => 'required_if:role,formateur|nullable|string',
        ]);

        // 2. Début de la transaction SQL pour garantir l'intégrité
        return DB::transaction(function () use ($request) {

            // Création du compte utilisateur
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'etat' => 'Actif',
            ]);

            // 3. Création du profil spécifique selon le rôle
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
                    'matricule' => $request->matricule, // 👈 AJOUTÉ
                ]);
            }

            // On recharge la relation pour renvoyer un objet complet au Front-end
            $profileRelation = $request->role . 'Profile';

            return response()->json([
                'message' => 'Utilisateur et profil créés avec succès',
                'user' => $user->load($profileRelation)
            ], 201);
        });
    }

    /**
     * Mise à jour de l'utilisateur.
     */
    public function update(Request $request, User $user)
    {
        $user->update($request->only(['email', 'etat']));
        return response()->json(['message' => 'Utilisateur mis à jour']);
    }

    /**
     * Suppression de l'utilisateur (Cascade Delete).
     */
    public function destroy(User $user) {
    $user->delete(); // Laravel va mettre la date dans 'deleted_at'
    return response()->json(['message' => 'Utilisateur archivé avec succès']);
}
//* Restauration d'un utilisateur supprimé (Soft Restore).
    //  * Note : Cette méthode n'est pas exposée dans les routes par défaut, tu peux l'ajouter si besoin.

public function restore($id)
{
    $user = User::withTrashed()->findOrFail($id);
    $user->restore();
    return response()->json(['message' => 'Utilisateur restauré', 'user' => $user->load('stagiaireProfile', 'formateurProfile')]);
}

public function resetPassword(Request $request, User $user)
{
    $request->validate([
        'password' => 'required|string|min:6',
    ]);

    $user->update([
        'password' => Hash::make($request->password)
    ]);

    return response()->json(['message' => 'Mot de passe mis à jour']);
}
}
