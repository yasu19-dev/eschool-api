<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\DemandeAttestation;
use Illuminate\Http\Request;

class ResponsableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // ResponsableController.php

public function index()
{
    // Statistiques pour le Responsable
    return response()->json([
        'justifications_attente' => Absence::where('est_justifie', false)->count(),
        'demandes_attestations' => DemandeAttestation::where('statut', 'En attente')->count(),
    ]);
}

// Validation d'une attestation
public function getPendingJustifications() {
        return Absence::where('est_justifie', false)->with(['stagiaire', 'seance.module'])->get();
    }

    public function validateAbsence(Absence $absence) {
        $absence->update(['est_justifie' => true]);
        return response()->json(['message' => 'Justifié']);
    }

    public function validateAttestation($id)
{
    $demande = DemandeAttestation::findOrFail($id);
    //correction ✅
    // $demande->update(['statut' => 'Prête', 'date_edition' => now()]); // incorrect
    $demande->update([
            'status' => 'Prête pour récupération',
            'date_livraison_prevue' => now()
        ]);

    return response()->json(['message' => 'L\'attestation est marquée comme prête.']);
}

    public function getPendingAttestations() {
        return DemandeAttestation::where('statut', 'En attente')->with('stagiaire')->get();
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
}
