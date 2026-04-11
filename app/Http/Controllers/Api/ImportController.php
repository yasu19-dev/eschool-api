<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\StagiaireImport;
use App\Services\GroupeModuleService;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TimetableImport;
use App\Models\Seance;
use Exception;

class ImportController extends Controller
{
    /**
     * Importation massive des stagiaires.
     */
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



    /**
 * Importation de l'emploi du temps global.
 * Cette version préserve l'historique des séances et des absences associées.
 */


    public function importTimetable(Request $request) {
        $request->validate(['fichier_excel' => 'required|mimes:xlsx,xls,csv|max:10240']);
        try {
           
            Excel::import(new TimetableImport, $request->file('fichier_excel'));
            return response()->json(['message' => 'L\'emploi du temps global a été mis à jour avec succès.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de l\'importation : ' . $e->getMessage()], 500);
        }
    }
// public function importTimetable(Request $request)
// {
//     // Validation stricte du format et de la taille du fichier
//     $request->validate([
//         'fichier_excel' => 'required|mimes:xlsx,xls,csv|max:10240'
//     ]);

//     try {
//         /* * ✅ Note logique : Nous avons retiré Seance::truncate().
//          * Cela permet d'ajouter les nouvelles séances de la semaine
//          * sans supprimer les séances passées auxquelles sont liées vos absences.
//          */

//         // Exécution de l'importation via Maatwebsite Excel
//         Excel::import(new TimetableImport, $request->file('fichier_excel'));

//         return response()->json([
//             'message' => 'L\'emploi du temps a été mis à jour avec succès. L\'historique des absences est préservé.'
//         ], 200);

//     } catch (\Exception $e) {
//         // Capture et retourne l'erreur précise (ex: format de date invalide ou ID introuvable)
//         return response()->json([
//             'message' => 'Erreur lors de l\'importation : ' . $e->getMessage()
//         ], 500);
//     }
// }

    // Tu peux supprimer ou laisser les méthodes vides si tu n'en as pas besoin
    public function index() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
