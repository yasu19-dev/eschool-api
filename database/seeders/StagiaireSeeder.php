<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\StagiaireProfile;
use App\Models\Groupe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StagiaireSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');
        $groupeID = Groupe::where('code', 'DEVOWFS201')->first()->id;

        // Liste des stagiaires
        $stagiaires = [
            ['nom' => 'SAOUSAOU', 'prenom' => 'Zaid', 'email' => 'zaid@ismontic.ma'],
            ['nom' => 'HARROUDI', 'prenom' => 'Yasmine', 'email' => 'yasmine.h@ismontic.ma'],
        ];

        foreach ($stagiaires as $s) {
            $user = User::create([
                'email' => $s['email'],
                'password' => $password,
                'role' => 'stagiaire', // Rôle direct
            ]);

            StagiaireProfile::create([
                'user_id' => $user->id,
                'groupe_id' => $groupeID,
                'cef' => rand(1000000, 9999999),
                'nom' => $s['nom'],
                'prenom' => $s['prenom'],
            ]);
        }
    }
}
