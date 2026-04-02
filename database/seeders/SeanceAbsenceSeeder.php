<?php

namespace Database\Seeders;

use App\Models\Seance;
use App\Models\Absence;
use App\Models\StagiaireProfile;
use App\Models\FormateurProfile;
use App\Models\Module;
use App\Models\Groupe;
use Illuminate\Database\Seeder;

class SeanceAbsenceSeeder extends Seeder
{
    public function run(): void
    {
        $formateur = FormateurProfile::first();
        $groupe = Groupe::where('code', 'DEVOWFS201')->first();
        $module = Module::first();

        // Création d'une séance de test
        $seance = Seance::create([
            'formateur_id' => $formateur->id,
            'module_id' => $module->id,
            'groupe_id' => $groupe->id,
            'date' => now()->format('Y-m-d'),
            'creneau' => '08:30 - 10:30',
            'type' => 'Cours',
            'salle' => 'Salle 101',
        ]);

        // Génération d'absences avec la nouvelle logique
        $stagiaires = StagiaireProfile::where('groupe_id', $groupe->id)->get();

        foreach ($stagiaires as $index => $stagiaire) {
            // On simule différents cas pour ta démo :
            Absence::create([
                'seance_id' => $seance->id,
                'stagiaire_id' => $stagiaire->id,
                // Si index est 0 -> Retard (Présent)
                // Sinon -> Absent (car est_en_retard = false)
                'est_en_retard' => ($index === 0),
                'est_justifie' => false,
                'motif' => ($index === 0) ? 'Problème de transport' : null,
            ]);
        }
    }
}
