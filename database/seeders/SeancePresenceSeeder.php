<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Seance;
use App\Models\Presence;
use App\Models\StagiaireProfile;
use App\Models\FormateurProfile;
use App\Models\Module;
use App\Models\Groupe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SeancePresenceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. On récupère les IDs nécessaires
        $formateur = FormateurProfile::where('matricule', 'F2015042')->first();
        $roleStagiaire = Role::where('code', 'stagiaire')->first();
        $password = Hash::make('password123');

        // 2. Création des nouveaux étudiants de ta liste React (ceux qui n'existent pas encore)
        $nouveauxStudents = [
            ['name' => 'Oussama Tkitak', 'matricule' => 'ST2025004', 'email' => 'oussama@ismontic.ma'],
            ['name' => 'Anas Lazar', 'matricule' => 'ST2025005', 'email' => 'anas@ismontic.ma'],
            ['name' => 'Adnan Fahsi', 'matricule' => 'ST2025006', 'email' => 'adnan@ismontic.ma'],
            ['name' => 'Imane Tribak', 'matricule' => 'ST2025007', 'email' => 'imane@ismontic.ma'],
            ['name' => 'Amal Ettaliqui', 'matricule' => 'ST2025008', 'email' => 'amal@ismontic.ma'],
        ];

        // On les ajoute au groupe DEVOWFS201 (TDD201 dans ton React)
        $groupe = Groupe::where('code', 'DEVOWFS201')->first();

        foreach ($nouveauxStudents as $s) {
            $user = User::create([
                'email' => $s['email'],
                'password' => $password,
            ]);
            $user->roles()->attach($roleStagiaire->id);

            $nom_parts = explode(' ', $s['name']);
            StagiaireProfile::create([
                'user_id' => $user->id,
                'groupe_id' => $groupe->id,
                'cef' => $s['matricule'],
                'nom' => $nom_parts[1] ?? '',
                'prenom' => $nom_parts[0],
            ]);
        }

        // 3. Création de l'Historique des Séances (basé sur ton historique React)
        $historiqueData = [
            ['date' => '2026-03-10', 'timeSlot' => '08:30 - 10:30', 'module_code' => 'DWA201', 'type' => 'Cours'],
            ['date' => '2026-03-09', 'timeSlot' => '14:00 - 16:00', 'module_code' => 'PJV101', 'type' => 'TP'],
            ['date' => '2026-03-08', 'timeSlot' => '10:45 - 12:45', 'module_code' => 'FJS302', 'type' => 'Controle Continu'],
        ];

        foreach ($historiqueData as $data) {
            $module = Module::where('code', $data['module_code'])->first();

            $seance = Seance::create([
                'formateur_id' => $formateur->id,
                'module_id' => $module->id,
                'groupe_id' => $groupe->id,
                'date' => $data['date'],
                'creneau' => $data['timeSlot'],
                'type' => $data['type'],
                'salle' => 'Salle ' . rand(1, 10),
            ]);

            // 4. On génère des présences aléatoires pour chaque séance
            $stagiaires = StagiaireProfile::where('groupe_id', $groupe->id)->get();
            foreach ($stagiaires as $stagiaire) {
                Presence::create([
                    'seance_id' => $seance->id,
                    'stagiaire_id' => $stagiaire->id,
                    'est_absent' => (rand(1, 10) > 8), // 20% de chance d'être absent
                    'est_en_retard' => (rand(1, 10) > 9), // 10% de chance d'être en retard
                ]);
            }
        }
    }
}
