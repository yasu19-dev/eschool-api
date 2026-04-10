<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Groupe;

class GroupeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupes = [
            // --- 1ÈRE ANNÉE (Tronc Commun) ---
            ['code' => 'DEV101', 'annee' => '1ère année'],
            ['code' => 'DEV102', 'annee' => '1ère année'],
            ['code' => 'DEV103', 'annee' => '1ère année'],
            ['code' => 'INFO101', 'annee' => '1ère année'],
            ['code' => 'INFO102', 'annee' => '1ère année'],
            ['code' => 'ID101', 'annee' => '1ère année'],
            ['code' => 'ID102', 'annee' => '1ère année'],
            ['code' => 'AI101', 'annee' => '1ère année'],

            // --- 2ÈME ANNÉE (Spécialités spécifiques) ---
            ['code' => 'DEVWOFS201', 'annee' => '2ème année'], // Fullstack
            ['code' => 'DEVWOFS202', 'annee' => '2ème année'],
            ['code' => 'INFO201',    'annee' => '2ème année'],
            ['code' => 'INFO202',    'annee' => '2ème année'],
            ['code' => 'IDCC201',    'annee' => '2ème année'], // Cloud Computing
            ['code' => 'IDCS201',    'annee' => '2ème année'], // Cyber sécurité
            ['code' => 'IDRS201',    'annee' => '2ème année'], // Systèmes et Réseaux
            ['code' => 'AIOADA201',  'annee' => '2ème année'], // AI Data Analyst
        ];

        foreach ($groupes as $groupe) {
            // updateOrCreate permet de relancer le seeder sans créer de doublons
            Groupe::updateOrCreate(
                ['code' => $groupe['code']],
                ['annee' => $groupe['annee']]
            );
        }
    }
}
