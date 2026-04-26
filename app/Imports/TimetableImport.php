<?php

namespace App\Imports;

use App\Models\Seance;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\User;
use App\Models\FormateurProfile;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TimetableImport implements ToModel, WithHeadingRow, WithCustomCsvSettings
{
    // --- MÉMOIRE LOCALE ---
    // On va stocker les profs ici pour éviter les doublons pendant l'import
    private $formateursCache = [];

    public function getCsvSettings(): array
    {
        return ['delimiter' => ','];
    }

   public function model(array $row)
{
    // 1. Extraction et nettoyage des codes
    // Laravel Excel transforme "nom-formateur" en "nom_formateur"
    $groupeCode = isset($row['groupe']) ? trim($row['groupe']) : null;
    $moduleCode = isset($row['module']) ? trim($row['module']) : null;
    $nomFormateurRaw = isset($row['nom_formateur']) ? trim($row['nom_formateur']) : null;
    $horaire = isset($row['horaire']) ? trim($row['horaire']) : null;
    $salle = isset($row['salle']) ? trim($row['salle']) : null;

    // Si une information essentielle manque sur la ligne, on l'ignore
    if (!$groupeCode || !$moduleCode || !$nomFormateurRaw) {
        return null;
    }

    // 2. Recherche des relations en base de données
    $groupe = \App\Models\Groupe::where('code', $groupeCode)->first();
    $module = \App\Models\Module::where('code', $moduleCode)->first();

    // ⚠️ CRITIQUE : Si le groupe ou le module n'existe pas, on ne peut pas créer la séance
    if (!$groupe || !$module) {
        // Optionnel : tu peux mettre un \Log::error("Ligne ignorée : Groupe $groupeCode ou Module $moduleCode introuvable");
        return null;
    }

    // 3. Gestion de la Date (Conversion du format Excel 46006)
    try {
        $dateRaw = $row['date'];
        if (is_numeric($dateRaw)) {
            // Convertit le chiffre Excel en objet DateTime puis en string Y-m-d
            $dateFormatted = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateRaw)->format('Y-m-d');
        } else {
            // Si c'est déjà une string (ex: "2025-12-15")
            $dateFormatted = \Carbon\Carbon::parse($dateRaw)->format('Y-m-d');
        }
    } catch (\Exception $e) {
        return null; // Date invalide
    }

    // 4. Récupération du Formateur (via ta fonction de recherche floue)
    try {
        $formateur = $this->getFormateur($nomFormateurRaw);
    } catch (\Exception $e) {
        // Si le formateur n'existe pas, on ignore la ligne (ou on laisse l'exception remonter)
        return null;
    }

    // 5. Vérification des Doublons (Sécurité)
    // On ne veut pas importer deux fois la même séance pour le même groupe au même moment
    $existe = \App\Models\Seance::where('groupe_id', $groupe->id)
        ->where('date', $dateFormatted)
        ->where('creneau', $horaire)
        ->exists();

    if ($existe) {
        return null;
    }

    // 6. Création de la Séance
    return new \App\Models\Seance([
        'id'               => (string) \Illuminate\Support\Str::uuid(),
        'groupe_id'        => $groupe->id,
        'module_id'        => $module->id,
        'formateur_id'     => $formateur->id,
        'date'             => $dateFormatted,
        'creneau'          => $horaire,
        'salle'            => $salle,
        'type'             => 'Cours',
        'commentaire_prof' => $row['statut'] ?? null,
    ]);
}

private function getFormateur($nomRaw)
{
    // 1. Nettoyage de base : on enlève M./Mme et on réduit les espaces multiples
    $nomExcel = preg_replace('/^(Mme|M|Mr)\.?\s+/i', '', $nomRaw);
    $nomExcel = strtoupper(trim(preg_replace('/\s+/', ' ', $nomExcel)));

    if (empty($nomExcel)) {
        throw new \Exception("Le nom du formateur est vide dans une ligne du fichier.");
    }

    // 2. Tentative de recherche par concaténation (Nom Prénom ou Prénom Nom)
    $profile = FormateurProfile::where(function($query) use ($nomExcel) {
        $query->where(DB::raw("UPPER(CONCAT(nom, ' ', prenom))"), $nomExcel)
              ->orWhere(DB::raw("UPPER(CONCAT(prenom, ' ', nom))"), $nomExcel);
    })->first();

    // 3. Si non trouvé, on tente une recherche par "mots-clés" (plus souple pour EL AFIFI)
    if (!$profile) {
        $words = explode(' ', $nomExcel); // ["EL", "AFIFI", "RACHIDA"]

        $profile = FormateurProfile::where(function($query) use ($words) {
            foreach ($words as $word) {
                if (strlen($word) > 2) { // On ignore les "M" ou "LE" trop courts
                    $query->where(function($q) use ($word) {
                        $q->where('nom', 'LIKE', '%' . $word . '%')
                          ->orWhere('prenom', 'LIKE', '%' . $word . '%');
                    });
                }
            }
        })->first();
    }

    // 4. Si toujours rien, on lève l'erreur
    if (!$profile) {
        throw new \Exception("Le formateur '$nomExcel' n'existe pas. Vérifiez l'orthographe dans votre liste d'utilisateurs.");
    }

    return $profile;
}
}
