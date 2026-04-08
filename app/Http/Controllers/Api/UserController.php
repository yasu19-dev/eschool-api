<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Liste générale des utilisateurs (si besoin hors panneau directeur).
     */
    public function index()
    {
        return User::with(['adminProfile', 'formateurProfile', 'stagiaireProfile'])->get();
    }

    /**
     * Voir les détails d'un utilisateur spécifique.
     */
    public function show(User $user)
    {
        return response()->json($user->load(['adminProfile', 'formateurProfile', 'stagiaireProfile']));
    }

    /**
     * Mise à jour des informations de compte (Email, État).
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'etat' => 'sometimes|in:Actif,Inactif',
        ]);

        $user->update($request->only(['email', 'etat']));

        return response()->json([
            'message' => 'Compte mis à jour avec succès',
            'user' => $user
        ]);
    }

    /**
     * Réinitialisation du mot de passe (La fonction qu'on a testée en React).
     * Route: PUT /api/director/users/{user}/reset-password
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        // 🔐 Hashage du nouveau mot de passe
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès pour ' . $user->email
        ]);
    }

    /**
     * Suppression définitive (si tu ne veux pas utiliser le SoftDelete du DirectorController).
     */
    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete();

        return response()->json(['message' => 'Utilisateur supprimé définitivement de la base de données']);
    }
}   
