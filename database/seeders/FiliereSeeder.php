<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Filiere;
use App\Models\Institution;
use Illuminate\Support\Str;

class FiliereSeeder extends Seeder
{
    public function run()
    {
        $instId = Institution::first()->id ?? Str::uuid();

        $filieres = [
            // --- DÉVELOPPEMENT DIGITAL ---
            ['title' => 'Développement Digital', 'specialite' => 'Tronc Commun', 'code' => 'DEV', 'color' => '#1E88E5', 'modules' => ['EGTS101', 'EGTS102', 'M102', 'M104']],
            ['title' => 'Développement Digital', 'specialite' => 'Fullstack', 'code' => 'DEVFS', 'color' => '#1565C0', 'modules' => ['EGTS202', 'M204', 'M205']],
            ['title' => 'Développement Digital', 'specialite' => 'Mobile', 'code' => 'DEVMO', 'color' => '#0D47A1', 'modules' => []],

            // --- INFRASTRUCTURE DIGITALE ---
            ['title' => 'Infrastructure Digitale', 'specialite' => 'Tronc Commun', 'code' => 'ID', 'color' => '#2E7D32', 'modules' => []],
            ['title' => 'Infrastructure Digitale', 'specialite' => 'Cloud Computing', 'code' => 'IDCC', 'color' => '#1B5E20', 'modules' => []],
            ['title' => 'Infrastructure Digitale', 'specialite' => 'Cyber sécurité', 'code' => 'IDCS', 'color' => '#004D40', 'modules' => []],
            ['title' => 'Infrastructure Digitale', 'specialite' => 'Systèmes et Réseaux', 'code' => 'IDRS', 'color' => '#00695C', 'modules' => []],

            // --- INFOGRAPHIE ---
            ['title' => 'Infographie', 'specialite' => 'Tronc Commun', 'code' => 'INFO', 'color' => '#F4511E', 'modules' => []],
            ['title' => 'Infographie', 'specialite' => 'Option Design', 'code' => 'INFO2', 'color' => '#BF360C', 'modules' => []],

            // --- AI ---
            ['title' => 'Artificial Intelligence', 'specialite' => 'Tronc Commun', 'code' => 'AI', 'color' => '#6A1B9A', 'modules' => []],
            ['title' => 'Artificial Intelligence', 'specialite' => 'Data Analyst', 'code' => 'AIOA', 'color' => '#4A148C', 'modules' => []],
        ];

      foreach ($filieres as $f) {
    Filiere::updateOrCreate(
        ['code' => $f['code']],
        [
            'institution_id' => $instId,
            'title'          => $f['title'],
            'specialite'     => $f['specialite'],
            'niveau'         => 'Technicien Spécialisé',
            'description'    => "Formation {$f['title']} - {$f['specialite']}",
            // ✅ PAS DE json_encode ICI, juste le tableau brut
            'modules'        => $f['modules'],
            'debouches'      => [],
            'color'          => $f['color'],
            'duration'       => '2 ans'
        ]
    );
}
    }
}
