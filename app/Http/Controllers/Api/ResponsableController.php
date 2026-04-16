<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\DemandeAttestation;
use Illuminate\Http\Request;
use App\Models\Groupe;
use App\Models\User;
use App\Models\Justificatif;
use App\Models\FormateurProfile;
use App\Models\StagiaireProfile;
use App\Models\Seance;
use  Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class ResponsableController extends Controller
{
    /**
     * Statistiques pour le dashboard global du Responsable
     */


    // ==========================================
    // GESTION DES ABSENCES
    // ==========================================
   public function getPendingJustifications() {

        return Absence::where('est_justifie', false)->with(['stagiaire', 'seance.module'])->get();
    }

public function index()
    {
        try {
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');

            // 1. KPIs
            $totalStagiaires = StagiaireProfile::count();
            // justificatifs utilise 'statut'
            $justificationsAttente = Justificatif::where('statut', 'En attente')->count();
            // demande_attestations utilise 'status'
            $demandesAttestations = DemandeAttestation::where('status', 'En attente')->count();

            // 2. Taux d'Absence Global
            $absencesCount = Absence::where('est_en_retard', false)
                ->where('date', '>=', $startOfMonth)
                ->count();
            $seancesCount = Seance::where('date', '>=', $startOfMonth)->count();
            $totalPossible = $seancesCount * $totalStagiaires;
            $tauxAbsence = $totalPossible > 0 ? ($absencesCount / $totalPossible) * 100 : 0;

            // 3. Alertes simples
            $alerts = [];
            if ($justificationsAttente > 0) {
                $alerts[] = ['message' => "$justificationsAttente justifications à traiter.", 'severity' => 'medium'];
            }
            if ($demandesAttestations > 0) {
                $alerts[] = ['message' => "$demandesAttestations attestations en attente.", 'severity' => 'medium'];
            }

            // 4. Attestations récentes
            $recentAttestations = DemandeAttestation::with('stagiaire')
                ->latest()
                ->take(3)
                ->get()
                ->map(function($att) {
                    return [
                        'student' => $att->stagiaire ? $att->stagiaire->nom . ' ' . $att->stagiaire->prenom : 'Inconnu',
                        'type' => $att->type, // Utilise 'type' selon ton modèle
                        'status' => $att->status, // Utilise 'status'
                        'date' => $att->created_at->diffForHumans()
                    ];
                });

            return response()->json([
                'kpis' => [
                    'taux_absence' => round($tauxAbsence, 1) . '%',
                    'justifications' => $justificationsAttente,
                    'attestations' => $demandesAttestations,
                    'stagiaires' => $totalStagiaires
                ],
                'alerts' => $alerts,
                'recent_attestations' => $recentAttestations
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
// Validation d'une attestation


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





    //  ?? logique de emploi du temps pour le responsable
    // 1. Liste des groupes pour le filtre
public function getGroupes() {
    return Groupe::select('id', 'code')->orderBy('code', 'asc')->get();
}

// 2. Liste des formateurs pour le filtre
public function getFormateurs() {
    return User::where('role', 'formateur')
                ->with('formateurProfile')
                ->get();
}

// 3. Séances par groupe
public function getSeancesByGroupe($id) {
    return Seance::where('groupe_id', $id)
        ->with(['module', 'formateur'])
        ->get();
}

// 4. Séances par formateur
public function getSeancesByFormateur($id) {
    return Seance::where('formateur_id', $id)
        ->with(['module', 'groupe'])
        ->get();
}

// 5. Exportation PDF (Identique au Directeur)
public function exportGroupsPDF() {
    $groupes = Groupe::with(['seances.module', 'seances.formateur'])->orderBy('code')->get();
    $data = [
        'title' => 'PLANNING GLOBAL GROUPES (Consultation)',
        'date'  => date('d/m/Y'),
        'items' => $groupes->map(function($g) {
            return ['header' => "GROUPE : " . $g->code, 'grid' => $this->organizeSchedule($g->seances)];
        })
    ];
    return Pdf::loadView('pdf.master_schedule', $data)->setPaper('a4', 'landscape')->download('Planning_Groupes.pdf');
}

public function exportTeachersPDF() {
    $formateurs = FormateurProfile::with(['seances.module', 'seances.groupe'])->get();
    $data = [
        'title' => 'PLANNING GLOBAL FORMATEURS (Consultation)',
        'date'  => date('d/m/Y'),
        'items' => $formateurs->map(function($f) {
            return ['header' => "FORMATEUR : " . strtoupper($f->nom) . " " . $f->prenom, 'grid' => $this->organizeSchedule($f->seances)];
        })
    ];
    return Pdf::loadView('pdf.master_schedule', $data)->setPaper('a4', 'landscape')->download('Planning_Formateurs.pdf');
}

// Helper technique pour la grille (Copie conforme du Directeur)
private function organizeSchedule($seances) {
    $grid = [];
    $daysMap = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'];
    foreach ($seances as $s) {
        $dayNum = Carbon::parse($s->date)->dayOfWeekIso;
        if (isset($daysMap[$dayNum])) {
            $grid[$s->creneau][$daysMap[$dayNum]] = [
                'module' => $s->module?->intitule ?? 'Module',
                'salle'  => $s->salle ?? 'TBD',
                'info'   => $s->groupe ? "Gr: " . $s->groupe->code : ($s->formateur ? $s->formateur->nom : '')
            ];
        }
    }
    ksort($grid);
    return $grid;
}


// ?? la logique de justification des abscences pour le responsable
public function getJustificationStats() {
    return response()->json([
        'total' => Justificatif::count(),
        'en_attente' => Justificatif::where('statut', 'En attente')->count(),
        'approuvees' => Justificatif::where('statut', 'Justifié')->count(),
        'rejetees' => Justificatif::where('statut', 'Non justifié')->count(),
    ]);
}


public function getAllJustifications() {
    // On récupère les justificatifs avec les relations pour l'affichage complet
    return Justificatif::with(['stagiaireProfile.groupe', 'seance.module'])
        ->latest()
        ->get();
}

public function updateJustificationStatus(Request $request, $id) {
    $request->validate([
        'statut' => 'required|in:Justifié,Non justifié',
        'commentaire' => 'nullable|string'
    ]);

    return DB::transaction(function () use ($request, $id) {
        $justificatif = Justificatif::findOrFail($id);

        // 1. Mise à jour du justificatif
        $justificatif->update([
            'statut' => $request->statut,
            'est_valide' => $request->statut === 'Justifié'
        ]);

        // 2. Si approuvé, on met à jour l'absence liée
        if ($request->statut === 'Justifié') {
            Absence::where('stagiaire_id', $justificatif->stagiaire_id)
                ->where('seance_id', $justificatif->seance_id)
                ->update(['est_justifie' => true]);
        }

        return response()->json(['message' => 'Statut mis à jour avec succès']);
    });
}
}

