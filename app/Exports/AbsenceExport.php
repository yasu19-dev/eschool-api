<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class AbsenceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;

    /**
     * Reçoit la collection de données filtrées depuis le contrôleur.
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Retourne la collection de données à exporter.
     */
    public function collection() {
        return $this->data;
    }

    /**
     * 1. Définition des colonnes d'en-tête du fichier Excel.
     */
    public function headings(): array {
        return [
            "Date",
            "CEF",
            "Stagiaire",
            "Groupe",
            "Module",
            "Formateur",
            "Type",
            "Justification",
            "Motif"
        ];
    }

    /**
     * 2. Correspondance des colonnes (Mapping).
     * Utilise les alias définis dans la requête SQL du contrôleur.
     */
    public function map($absence): array {
        return [
            $absence->date,
            $absence->cef,
            $absence->s_nom . ' ' . $absence->s_prenom, // Nom complet stagiaire
            $absence->groupe,
            $absence->module,
            'Mr/Mme ' . $absence->f_prenom . ' ' . $absence->f_nom, // Nom complet formateur
            $absence->est_en_retard ? 'Retard' : 'Absence',
            $absence->est_justifie ? 'Justifiée' : 'Non justifiée',
            $absence->motif ?? '-'
        ];
    }

    /**
     * 3. Style de la page Excel.
     * Applique un fond vert (1D6F42) et du texte blanc en gras sur la première ligne.
     */
    public function styles(Worksheet $sheet) {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1D6F42']
                ]
            ],
        ];
    }
}
