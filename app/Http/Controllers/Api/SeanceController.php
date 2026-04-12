<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeanceController extends Controller
{
    /**
     * Récupère les séances du formateur avec les relations Groupe et Module.
     */
    public function index(Request $request)
    {
        // On récupère le profil du formateur connecté
        $formateur = $request->user()->formateurProfile;

        if (!$formateur) {
            return response()->json(['message' => 'Profil formateur non trouvé'], 404);
        }

        // On récupère les séances en chargeant (Eager Loading) le groupe et le module
        // C'est le "with" qui va supprimer le message "Code introuvable"
        $seances = Seance::where('formateur_id', $formateur->id)
            ->with(['groupe:id,code', 'module:id,intitule,code'])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($seances);
    }
}
