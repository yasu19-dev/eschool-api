<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use App\Models\DemandeAttestation;
use Illuminate\Http\Request;

class StagiaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

    // 1. Récupérer les notes du stagiaire connecté
    public function getNotes(Request $request)
    {
        $profile = $request->user()->stagiaireProfile;
        // On charge les notes avec les informations du module associé
        return response()->json($profile->notes()->with('module')->get());
    }

    // 2. Récupérer l'emploi du temps du groupe
    public function getSchedule(Request $request)
    {
        $profile = $request->user()->stagiaireProfile;

        // On récupère le dernier emploi du temps PDF uploadé pour son groupe
        $schedule = $profile->groupe->emploisDuTemps()->latest()->first();

        return response()->json([
            'groupe' => $profile->groupe->code,
            'schedule_url' => $schedule ? $schedule->fichier_url : null
        ]);
    }
    public function getAbsences(Request $request) {
        return response()->json($request->user()->stagiaireProfile->absences()->with('seance.module')->get());
    }

    // 3. Poster une réclamation
    public function postReclamation(Request $request)
    {
        $request->validate([
            'objet' => 'required|string|max:150',
            'message' => 'required|string',
            'type' => 'required|in:Note,Absence,Autre'
        ]);

        $reclamation = Reclamation::create([
            'stagiaire_id' => $request->user()->stagiaireProfile->id,
            'objet' => $request->objet,
            'message' => $request->message,
            'type' => $request->type,
            'statut' => 'En attente'
        ]);

        return response()->json(['message' => 'Réclamation envoyée !', 'data' => $reclamation], 201);
    }

    // 4. Demander une attestation
    public function postAttestation(Request $request)
    {
        $request->validate([
            'type' => 'required|in:Scolarité,Réussite,Stage',
        ]);

        $demande = DemandeAttestation::create([
            'stagiaire_id' => $request->user()->stagiaireProfile->id,
            'type' => $request->type,
            'statut' => 'En attente'
        ]);

        return response()->json(['message' => 'Demande d\'attestation enregistrée !', 'data' => $demande], 201);
    }
}
