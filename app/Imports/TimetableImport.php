<?php

namespace App\Imports;

use App\Models\Seance;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\FormateurProfile;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Exception;
// ✅ Outil pour convertir les dates numériques spécifiques à Excel
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class TimetableImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws Exception
     */
    public function model(array $row)
    {
        // 1. Recherche du Groupe (par son code, ex: DEVOWFS201)
        $groupe = Groupe::where('code', $row['groupe'])->first();
        if (!$groupe) {
            throw new Exception("Erreur : Le groupe '{$row['groupe']}' n'existe pas dans la table groupes.");
        }

        // 2. Recherche du Module (par son code, ex: M204)
        $module = Module::where('code', $row['code_module'])->first();
        if (!$module) {
            throw new Exception("Erreur : Le module avec le code '{$row['code_module']}' est introuvable.");
        }

        // 3. Recherche du Formateur (par son matricule, ex: F2015042)
        $formateur = FormateurProfile::where('matricule', $row['matricule_formateur'])->first();
        if (!$formateur) {
            throw new Exception("Erreur : Le formateur avec le matricule '{$row['matricule_formateur']}' n'existe pas.");
        }

        // 4. LOGIQUE DE CORRECTION DE LA DATE ✅
        // Empêche l'enregistrement de dates erronées (1970/1997) dues au format Excel
        try {
            if (is_numeric($row['date'])) {
                // Conversion du format numérique Excel en objet Date PHP puis format MySQL
                $dateFormatted = ExcelDate::excelToDateTimeObject($row['date'])->format('Y-m-d');
            } else {
                // Si la date est au format texte "JJ/MM/AAAA"
                $dateFormatted = Carbon::createFromFormat('d/m/Y', $row['date'])->format('Y-m-d');
            }
        } catch (Exception $e) {
            // En cas de format de texte différent (ex: AAAA-MM-JJ)
            $dateFormatted = Carbon::parse($row['date'])->format('Y-m-d');
        }

        // 5. Création de la séance en base de données
        return new Seance([
            'groupe_id'        => $groupe->id,
            'module_id'        => $module->id,
            'formateur_id'     => $formateur->id,
            'date'             => $dateFormatted,
           'creneau'          => $row['horaire'],
           'type'             => 'Cours',
            'salle'            => $row['salle'],   // ex: SDD1 ou A DISTANCE
            'commentaire_prof' => $row['notes'] ?? null,
        ]);
    }
}
