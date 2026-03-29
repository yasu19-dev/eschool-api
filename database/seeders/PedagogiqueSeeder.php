<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FormateurProfile;
use App\Models\Module;
use App\Models\Groupe;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PedagogiqueSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        // --- 1. CRÉATION DES GROUPES ---
        $groupes = ['DEVOWFS201', 'DEVOWFS202', 'DEVOWFS203', 'DEV103', 'DEV104'];
        foreach ($groupes as $nom) {
            Groupe::firstOrCreate([
                'code' => $nom,
                'annee' => str_contains($nom, '20') ? '2ème année' : '1ère année'
            ]);
        }

       
       // --- 2. CRÉATION DES MODULES ---
        $modules = [
            ['code' => 'DWA201', 'intitule' => 'Développement Front-end', 'masse_horaire' => 120],
            ['code' => 'PJV101', 'intitule' => 'Programmation Javascript', 'masse_horaire' => 90],
            ['code' => 'FJS302', 'intitule' => 'Base de données', 'masse_horaire' => 100],
            ['code' => 'DMB201', 'intitule' => 'Développement back-end', 'masse_horaire' => 110],
        ];

        foreach ($modules as $m) {
            Module::firstOrCreate(['code' => $m['code']], $m);
        }

        // --- 3. CRÉATION DU COMPTE FORMATEUR (Mme Bouchra) ---
        // Récupérer le rôle formateur
        $roleFormateur = Role::where('code', 'formateur')->first();

        // Créer l'utilisateur
        $prof = User::create([
            'email' => 'bouchra.elakel@ismontic.ma',
            'password' => $password,
        ]);

        // Lier le rôle à la prof
        $prof->roles()->attach($roleFormateur->id);

        FormateurProfile::create([
            'user_id' => $prof->id,
            'matricule' => 'F2015042',
            'nom' => 'EL AKEL',
            'prenom' => 'Bouchra',
            'email_professionnel' => 'bouchra.elakel@ismontic.ma',
            'cin' => 'CD987654',
            'date_naissance' => '1985-11-12',
            'lieu_naissance' => 'Tanger',
            'specialite' => 'Développement Web & Mobile',
            'grade' => 'Formatrice Principale',
            'date_recrutement' => '2000-09-15',
            'departement' => 'Développement Digital',
        ]);


    }
}
