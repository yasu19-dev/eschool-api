<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // On cherche l'utilisateur sans charger la relation 'roles' qui n'existe plus
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }

        // --- 🆕 MODIFICATION : MISE À JOUR DE LA DERNIÈRE CONNEXION ---
        // On enregistre l'heure actuelle au moment précis où le mot de passe est validé
        $user->update([
            'lastLogin' => now()
        ]);
        // Création du token via Sanctum (compatible UUID)
        $token = $user->createToken('ismontic_token')->plainTextToken;

        // Le rôle est maintenant un champ direct dans la table users
        $roleCode = $user->role;

        // Récupération du profil et du sous-rôle pour les admins
        $profile = null;
        $adminSubRole = null;

        if ($user->isAdmin()) {
            $profile = $user->adminProfile; // Utilisation de la relation Eloquent
            $adminSubRole = $profile ? $profile->role_admin : null; // 'directeur' ou 'responsable_stagiaire'
        } elseif ($user->isFormateur()) {
            $profile = $user->formateurProfile;
        } elseif ($user->isStagiaire()) {
            $profile = $user->stagiaireProfile;
        }

        // On attache les infos pour le Front-End React
        $user->nom = $profile ? $profile->nom : '';
        $user->prenom = $profile ? $profile->prenom : '';

        /** * Important : On ajoute 'adminSubRole' pour que la Sidebar React
         * puisse filtrer les menus correctement !
         */
        $user->adminSubRole = $adminSubRole;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'role' => $roleCode // admin, formateur ou stagiaire
        ]);
    }

    public function me(Request $request)
    {
        // On renvoie l'utilisateur avec ses informations de profil chargées
        $user = $request->user();

        if ($user->isAdmin()) {
            $user->load('adminProfile');
            $user->adminSubRole = $user->adminProfile->role_admin;
        } elseif ($user->isFormateur()) {
            $user->load('formateurProfile');
        } else {
            $user->load('stagiaireProfile');
        }

        return response()->json($user);
    }

     public function logout(Request $request)

    {

        // On extrait le token et on indique explicitement sa vraie classe à VS Code

        /** @var \Laravel\Sanctum\PersonalAccessToken $token */

        $token = $request->user()->currentAccessToken();



        // La ligne rouge va disparaître car PersonalAccessToken possède bien la méthode delete() !

        $token->delete();



        return response()->json(['message' => 'Déconnexion réussie']);

    }
}
