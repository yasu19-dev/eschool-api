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

    // On charge l'utilisateur AVEC ses rôles associés
    $user = User::with('roles')->where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Identifiants incorrects'], 401);
    }

    $token = $user->createToken('eschool_token')->plainTextToken;

    // On récupère le nom du premier rôle (je suppose que ta table 'roles' a une colonne 'nom' ou 'name')
    // On le met en minuscules pour correspondre à tes routes React (admin, formateur, stagiaire)
    $roleName = $user->roles->first() ? strtolower($user->roles->first()->code) : 'stagiaire';

    return response()->json([
        'token' => $token,
        'user' => $user,
        'role' => $roleName 
    ]);
}

    public function me(Request $request)
    {
        return response()->json($request->user());
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
