<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::with('roles')->where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Identifiants incorrects'], 401);
    }

    $token = $user->createToken('eschool_token')->plainTextToken;
    $roleCode = $user->roles->first() ? strtolower($user->roles->first()->code) : 'stagiaire';

    // 1. On va chercher le profil en fonction du rôle
    $profile = null;
    if ($roleCode === 'admin') {
        $profile = DB::table('admin_profiles')->where('user_id', $user->id)->first();
    } elseif ($roleCode === 'formateur') {
        $profile = DB::table('formateur_profiles')->where('user_id', $user->id)->first();
    } elseif ($roleCode === 'stagiaire') {
        $profile = DB::table('stagiaire_profiles')->where('user_id', $user->id)->first();
    }

    // 2. On attache le nom et prénom directement à l'objet utilisateur
    // On met des chaînes vides par défaut au cas où le profil n'a pas encore été rempli par Aya
    $user->nom = $profile ? $profile->nom : '';
    $user->prenom = $profile ? $profile->prenom : '';

    return response()->json([
        'token' => $token,
        'user' => $user,
        'role' => $roleCode
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
