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

    /**
 * Valider une absence (Passer est_justifie de 0 à 1)
 * Route: PATCH /api/responsable-stagiaire/absences/{id}/validate
 */
public function validateAbsence($id)
{
    try {
        // 1. On cherche l'absence par son UUID
        // Si l'ID n'existe pas, Laravel renvoie automatiquement une erreur 404
        $absence = \App\Models\Absence::findOrFail($id);

        // 2. Mise à jour de la colonne 'est_justifie'
        // On passe la valeur à 1 (Vrai)
        $absence->update([
            'est_justifie' => 1
        ]);

        // 3. Retour de la réponse en JSON pour Postman/React
        return response()->json([
            'status' => 'success',
            'message' => 'L\'absence a été validée avec succès.',
            'data' => [
                'absence_id' => $absence->id,
                'nouveau_statut' => $absence->est_justifie
            ]
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Si l'UUID copié dans Postman est faux
        return response()->json([
            'status' => 'error',
            'message' => 'Absence introuvable. Vérifiez l\'ID envoyé.'
        ], 404);

    } catch (\Exception $e) {
        // Pour toute autre erreur technique (500)
        return response()->json([
            'status' => 'error',
            'message' => 'Erreur technique : ' . $e->getMessage()
        ], 500);
    }
}

   public function validateAttestation(Request $request, $id)
{
    try {
        // 1. Cherche la demande par son UUID
        $demande = \App\Models\DemandeAttestation::findOrFail($id);

        // 2. Mise à jour selon les ENUM de ta migration
        $demande->update([
            'status' => 'Prête pour récupération',
            // On peut aussi mettre à jour la date si elle est envoyée depuis React/Postman
            'date_livraison_prevue' => $request->input('date_livraison', now()->addDays(2))
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'L\'attestation est prête. Le stagiaire peut venir la récupérer.',
            'data' => [
                'id' => $demande->id,
                'nouveau_statut' => $demande->status,
                'disponible_le' => $demande->date_livraison_prevue
            ]
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => "ID invalide : Aucune demande d'attestation trouvée avec cet UUID."
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

   public function getPendingAttestations()
{
    try {
        // On teste la requête étape par étape
        return \App\Models\DemandeAttestation::where('status', 'En attente')
            ->with(['stagiaire.user']) // On récupère le stagiaire ET son nom/email
            ->get();

    } catch (\Exception $e) {
        // Si ça plante, on voit le message en JSON dans Postman
        return response()->json([
            'status' => 'Erreur GET Attestations',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
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
