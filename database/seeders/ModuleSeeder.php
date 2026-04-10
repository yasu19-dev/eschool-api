<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use Illuminate\Support\Str;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            // --- 1ÈRE ANNÉE ---
            ['code' => 'EGTS101', 'intitule' => 'Arabe', 'coefficient' => 1, 'masse_horaire' => 30],
            ['code' => 'EGTS102', 'intitule' => 'Français', 'coefficient' => 2, 'masse_horaire' => 120],
            ['code' => 'EGTS103', 'intitule' => 'Anglais technique', 'coefficient' => 2, 'masse_horaire' => 50],
            ['code' => 'EGTS105', 'intitule' => 'Compétences comportementales et sociales', 'coefficient' => 2, 'masse_horaire' => 30],
            ['code' => 'EGTS108', 'intitule' => 'Entrepreneuriat-PIE 1', 'coefficient' => 2, 'masse_horaire' => 60],
            ['code' => 'EGTSA106', 'intitule' => 'Culture et techniques avancées du numérique', 'coefficient' => 1, 'masse_horaire' => 30],
            ['code' => 'M101', 'intitule' => 'Se situer au regard du métier et de la démarche de formation', 'coefficient' => 1, 'masse_horaire' => 30],
            ['code' => 'M102', 'intitule' => 'Acquérir les bases de l’algorithmique', 'coefficient' => 2, 'masse_horaire' => 120],
            ['code' => 'M103', 'intitule' => 'Programmer en Orienté Objet', 'coefficient' => 2, 'masse_horaire' => 120],
            ['code' => 'M104', 'intitule' => 'Développer des sites web statiques', 'coefficient' => 3, 'masse_horaire' => 110],
            ['code' => 'M105', 'intitule' => 'Programmer en JavaScript', 'coefficient' => 3, 'masse_horaire' => 110],
            ['code' => 'M106', 'intitule' => 'Manipuler des bases de données', 'coefficient' => 2, 'masse_horaire' => 100],
            ['code' => 'M107', 'intitule' => 'Développer des sites web dynamiques', 'coefficient' => 3, 'masse_horaire' => 120],
            ['code' => 'M108', 'intitule' => 'S’initier à la sécurité des systèmes d’information', 'coefficient' => 2, 'masse_horaire' => 80],

            // --- 2ÈME ANNÉE ---
            ['code' => 'EGTS202', 'intitule' => 'Français', 'coefficient' => 2, 'masse_horaire' => 115],
            ['code' => 'EGTS203', 'intitule' => 'Anglais technique', 'coefficient' => 2, 'masse_horaire' => 50],
            ['code' => 'EGTS205', 'intitule' => 'Compétences comportementales', 'coefficient' => 2, 'masse_horaire' => 30],
            ['code' => 'EGTS208', 'intitule' => 'Entrepreneuriat-PIE 2', 'coefficient' => 2, 'masse_horaire' => 80],
            ['code' => 'EGTSA206', 'intitule' => 'Culture et techniques avancées du numérique', 'coefficient' => 1, 'masse_horaire' => 30],
            ['code' => 'M201', 'intitule' => 'Préparation d’un projet web', 'coefficient' => 1, 'masse_horaire' => 60],
            ['code' => 'M202', 'intitule' => 'Approche agile', 'coefficient' => 3, 'masse_horaire' => 120],
            ['code' => 'M203', 'intitule' => 'Gestion des données', 'coefficient' => 2, 'masse_horaire' => 90],
            ['code' => 'M204', 'intitule' => 'Développement front-end', 'coefficient' => 3, 'masse_horaire' => 90],
            ['code' => 'M205', 'intitule' => 'Développement back-end', 'coefficient' => 3, 'masse_horaire' => 120],
            ['code' => 'M206', 'intitule' => 'Création d’une application Cloud native', 'coefficient' => 2, 'masse_horaire' => 90],
            ['code' => 'M207', 'intitule' => 'Projet de synthèse', 'coefficient' => 2, 'masse_horaire' => 60],
            ['code' => 'M208', 'intitule' => 'Intégration du milieu professionnel', 'coefficient' => 2, 'masse_horaire' => 160],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(['code' => $module['code']], $module);
        }
    }
}
