<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\StagiaireImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TimetableImport;
use App\Models\Seance;
use Exception;

class ImportController extends Controller
{
    public function importStagiaires(Request $request)
    {
        // Validation stricte du fichier
        $request->validate([
            'fichier_excel' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            // Exécution de l'importation
            Excel::import(new StagiaireImport, $request->file('fichier_excel'));

            return response()->json([
                'message' => 'L\'importation des stagiaires a été effectuée avec succès.'
            ], 200);

        } catch (Exception $e) {
            // Retourne l'erreur précise en cas de problème (ex: email déjà pris)
            return response()->json([
                'message' => 'Erreur lors de l\'importation : ' . $e->getMessage()
            ], 500);
        }
    }
    public function importTimetable(Request $request)
{
    $request->validate([
        'fichier_excel' => 'required|mimes:xlsx,xls,csv|max:10240'
    ]);

    try {
        // OPTIONNEL : On vide les séances existantes si on veut un renouvellement total
        // Seance::truncate();

        Excel::import(new TimetableImport, $request->file('fichier_excel'));

        return response()->json([
            'message' => 'L\'emploi du temps global a été mis à jour avec succès.'
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de l\'importation : ' . $e->getMessage()
        ], 500);
    }
}

    // Tu peux supprimer ou laisser les méthodes vides si tu n'en as pas besoin
    public function index() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
