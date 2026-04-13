<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\DemandeAttestation;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ResponsableController extends Controller
{
    /**
     * Statistiques pour le dashboard global du Responsable
     */
    public function index()
    {
        return response()->json([
            'justifications_attente' => Absence::where('est_justifie', false)->count(),
            // CORRECTION: 'statut' remplacé par 'status'
            'demandes_attestations' => DemandeAttestation::where('status', 'En attente')->count(),
        ]);
    }

    // ==========================================
    // GESTION DES ABSENCES
    // ==========================================
    public function getPendingJustifications()
    {
        return Absence::where('est_justifie', false)->with(['stagiaire', 'seance.module'])->get();
    }

    public function validateAbsence(Absence $absence)
    {
        $absence->update(['est_justifie' => true]);
        return response()->json(['message' => 'Justifié']);
    }

    // ==========================================
    // GESTION DES ATTESTATIONS (NOUVELLE LOGIQUE)
    // ==========================================

    /**
     * Récupère TOUTES les attestations pour le tableau React
     */
    public function getAttestations()
    {
        // On charge la relation stagiaire ET la relation user à l'intérieur du stagiaire
        // pour pouvoir afficher le nom complet et le matricule dans React.
        $attestations = DemandeAttestation::with('stagiaire.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($attestations);
    }

    /**
     * Met à jour dynamiquement le statut (Validée, Refusée, Prête, Livrée...)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'motif_refus' => 'nullable|string'
        ]);

        $demande = DemandeAttestation::findOrFail($id);

        // Mise à jour du statut
        $demande->status = $request->status;

        // Si le statut est "Refusée", on enregistre le motif
        if ($request->status === 'Refusée' && $request->filled('motif_refus')) {
            $demande->motif_refus = $request->motif_refus;
        }

        // Si le statut est "Prête pour récupération", on peut set la date
        if ($request->status === 'Prête pour récupération') {
            $demande->date_livraison_prevue = now();
        }

        $demande->save();

        return response()->json([
            'message' => 'Le statut a été mis à jour avec succès.',
            'data' => $demande
        ]);
    }
    // ==========================================
    // GÉNÉRER LE PDF (ATTESTATION DE SCOLARITÉ)
    // Route: GET /api/attestations/{id}/generate-pdf
    // ==========================================
    public function generatePdf($id)
    {
        $demande = DemandeAttestation::with(['stagiaire.groupe'])->findOrFail($id);

        if ($demande->type !== 'Attestation de scolarité') {
            return response()->json(['message' => 'Seul ce type de document peut être généré automatiquement.'], 403);
        }

        $stagiaire = $demande->stagiaire;

        // Formatage sécurisé des dates (évite les erreurs Carbon)
        $dateNaissance = 'N/A';
        if ($stagiaire->date_naissance) {
            $dateNaissance = date('d/m/Y', strtotime($stagiaire->date_naissance)) . ' à ' . $stagiaire->lieu_naissance;
        }

        $dateDebut = 'N/A';
        if ($stagiaire->date_inscription) {
            $dateDebut = date('d/m/Y', strtotime($stagiaire->date_inscription));
        }

        $data = [
            'nom_complet'     => strtoupper($stagiaire->nom . ' ' . $stagiaire->prenom),
            'date_naissance'  => $dateNaissance,
            'niveau'          => $stagiaire->groupe->nom ?? 'Technicien Spécialisé',
            'specialite'      => $stagiaire->groupe->filiere ?? 'Développement Digital',
            'annee'           => $stagiaire->annee_scolaire ?? '2025/2026',
            'matricule'       => $stagiaire->cef,
            'date_debut'      => $dateDebut,
            'date_generation' => now()->format('d/m/Y')
        ];

        // 🌟 L'ASTUCE EST ICI : On appelle le wrapper directement, VS Code adore ça !
        $pdf = app('dompdf.wrapper')->loadView('pdf.attestation', $data);

        return $pdf->download('Attestation_'.$data['matricule'].'.pdf');
    }
}
