<?php

namespace App\Imports;

use App\Models\Seance;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\FormateurProfile;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings; // ✅ Requis pour les CSV
use Carbon\Carbon;
use Exception;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class TimetableImport implements ToModel, WithHeadingRow, WithCustomCsvSettings
{
    // ✅ Force la lecture du CSV avec des virgules pour éviter les erreurs de lecture
    public function getCsvSettings(): array
    {
        return ['delimiter' => ','];
    }

    public function model(array $row)
    {
        // 1. Recherche du Groupe (Clé 'groupe' du CSV)
        $groupe = Groupe::where('code', $row['groupe'])->first();
        if (!$groupe) {
            throw new Exception("Erreur : Le groupe '{$row['groupe']}' est introuvable.");
        }

        // 2. Recherche du Module (Clé 'module' du CSV) ✅ Corrigé
        $module = Module::where('code', $row['module'])->first();
        if (!$module) {
            throw new Exception("Erreur : Le module '{$row['module']}' est introuvable.");
        }

        // 3. Recherche du Formateur par NOM ✅ Corrigé (puisque le CSV contient le nom)
        $nomFamille = str_replace(['Mme. ', 'M. '], '', $row['formateur']);
        $formateur = FormateurProfile::where('nom', 'like', '%' . trim($nomFamille) . '%')->first();

        if (!$formateur) {
            throw new Exception("Erreur : Le formateur '{$row['formateur']}' est introuvable dans la base.");
        }

        // 4. Logique de Date flexible ✅
        try {
            if (is_numeric($row['date'])) {
                $dateFormatted = ExcelDate::excelToDateTimeObject($row['date'])->format('Y-m-d');
            } else {
                // Carbon essaie de deviner le format si ce n'est pas du d/m/Y
                $dateFormatted = Carbon::parse($row['date'])->format('Y-m-d');
            }
        } catch (Exception $e) {
            throw new Exception("Erreur format date sur la ligne : " . $row['date']);
        }

        // 5. Création (ou mise à jour pour éviter les doublons)
        return Seance::updateOrCreate(
            [
                'groupe_id' => $groupe->id,
                'date'      => $dateFormatted,
                'creneau'   => $row['creneau'], // ✅ Corrigé (clé CSV 'creneau')
            ],
            [
                'id'           => (string) \Illuminate\Support\Str::uuid(),
                'module_id'    => $module->id,
                'formateur_id' => $formateur->id,
                'type'         => 'Cours',
                'salle'        => $row['salle'],
                'commentaire_prof' => $row['notes'] ?? null,
            ]
        );
    }
}
