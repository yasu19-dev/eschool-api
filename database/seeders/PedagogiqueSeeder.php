<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FormateurProfile;
use App\Models\Module;
use App\Models\Groupe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PedagogiqueSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        // Groupes & Modules (Inchangés)
        $groupes = ['DEVOWFS201', 'DEVOWFS202', 'DEV103', 'DEV104'];
        foreach ($groupes as $nom) {
            Groupe::firstOrCreate(['code' => $nom], ['annee' => '2ème année']);
        }

        $modules = [
            ['code' => 'DWA201', 'intitule' => 'Développement Front-end', 'masse_horaire' => 120],
            ['code' => 'DMB201', 'intitule' => 'Développement back-end', 'masse_horaire' => 110],
        ];
        foreach ($modules as $m) {
            Module::firstOrCreate(['code' => $m['code']], $m);
        }

        // --- FORMATEUR (Mme Bouchra) ---
        $prof = User::create([
            'email' => 'bouchra.elakel@ismontic.ma',
            'password' => $password,
            'role' => 'formateur', // Rôle direct
        ]);

        FormateurProfile::create([
            'user_id' => $prof->id,
            'matricule' => 'F2015042',
            'nom' => 'EL AKEL',
            'prenom' => 'Bouchra',
            'email_professionnel' => 'bouchra.elakel@ismontic.ma',
            'specialite' => 'Développement Digital',
        ]);
    }
}
