<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\StagiaireProfile;
use App\Models\Groupe;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StagiaireSeeder extends Seeder
{
    public function run(): void
    {

        $password = Hash::make('password123');
        $groupeID = Groupe::where('code', 'DEVOWFS201')->first()->id;

        // Récupérer le rôle stagiaire
        $roleStagiaire = Role::where('code', 'stagiaire')->first();

        // --- PROFIL DE ZAID ---
        $zaidUser = User::create([
            'email' => 'zaid.saousaou@ismontic.ma',
            'password' => $password,
        ]);
        $zaidUser->roles()->attach($roleStagiaire->id); // On l'attache ici !


        StagiaireProfile::create([
            'user_id' => $zaidUser->id,
            'groupe_id' => $groupeID,
            'cef' => '200051400448',
            'cin' => 'KB223621',
            'nom' => 'SAOUSAOU',
            'prenom' => 'Zaid',
            'date_naissance' => '2001-05-14',
            'lieu_naissance' => 'Tanger',
            'date_inscription' => '2024-09-01',
            'annee_scolaire' => '2025/2026',
        ]);

        // --- 2. PROFIL DE YASMINE ---
        $yasmineUser = User::create([
            'email' => 'yasmine.harroudi@ismontic.ma',
            'password' => $password,
        ]);
        $yasmineUser->roles()->attach($roleStagiaire->id);

        StagiaireProfile::create([
            'user_id' => $yasmineUser->id,
            'groupe_id' => $groupeID,
            'cef' => '200051400449',
            'nom' => 'HARROUDI',
            'prenom' => 'Yasmine',
        ]);

        // --- 3. PROFIL DE AYA
        $ayaUser = User::create([
            'email' => 'aya.belghazi@ismontic.ma',
            'password' => $password,
        ]);
        $ayaUser->roles()->attach($roleStagiaire->id);

        StagiaireProfile::create([
            'user_id' => $ayaUser->id,
            'groupe_id' => $groupeID,
            'cef' => '200051400450',
            'nom' => 'BELGHAZI',
            'prenom' => 'Aya',
        ]);
    }
}
