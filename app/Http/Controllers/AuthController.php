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

        // --- 🪄 LA RÉPARATION : EAGER LOADING ---
        // On charge officiellement la relation pour qu'elle apparaisse dans le JSON
        if ($user->isAdmin()) {
            $user->load('adminProfile');
            $profile = $user->adminProfile;
            $user->adminSubRole = $profile ? $profile->role_admin : null;
        } elseif ($user->isFormateur()) {
            $user->load('formateurProfile'); // Charge matricule, cin, bio, etc.
            $profile = $user->formateurProfile;
        } elseif ($user->isStagiaire()) {
            $user->load('stagiaireProfile');
            $profile = $user->stagiaireProfile;
        }

        // On crée une propriété 'name' propre pour éviter les bugs dans React
        $user->name = $profile ? ($profile->prenom . ' ' . $profile->nom) : 'Utilisateur';

        // On garde les propriétés individuelles pour la compatibilité
        $user->nom = $profile ? $profile->nom : '';
        $user->prenom = $profile ? $profile->prenom : '';

        return response()->json([
            'token' => $token,
            'user' => $user,
            'role' => $user->role
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        // On s'assure que le profil est rechargé si l'utilisateur rafraîchit la page
        if ($user->isAdmin()) {
            $user->load('adminProfile');
            $user->adminSubRole = $user->adminProfile->role_admin;
            $profile = $user->adminProfile;
        } elseif ($user->isFormateur()) {
            $user->load('formateurProfile');
            $profile = $user->formateurProfile;
        } else {
            $user->load('stagiaireProfile');
            $profile = $user->stagiaireProfile;
        }

        $user->name = $profile ? ($profile->prenom . ' ' . $profile->nom) : 'Utilisateur';

        return response()->json($user);
    }

    public function logout(Request $request)
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Déconnexion réussie']);
    }
}
