<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\DemandeAttestation;
use Illuminate\Http\Request;
use App\Models\Groupe;
use App\Models\User;
use App\Models\FormateurProfile;
use App\Models\Seance;
use  Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;


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
}

