<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AdminProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        // --- 1. LE DIRECTEUR ---
        $directorUser = User::create([
            'email' => 'admin@ismontic.ma',
            'password' => $password,
            'role' => 'admin', // Rôle direct
        ]);

        AdminProfile::create([
            'user_id' => $directorUser->id,
            'nom' => 'ALILOU',
            'prenom' => 'Saad',
            'role_admin' => 'directeur', // Sous-rôle
        ]);

        // --- 2. LE RESPONSABLE STAGIAIRE ---
        $managerUser = User::create([
            'email' => 'responsable@ismontic.ma',
            'password' => $password,
            'role' => 'admin',
        ]);

        AdminProfile::create([
            'user_id' => $managerUser->id,
            'nom' => 'AYADI',
            'prenom' => 'Hajiba',
            'role_admin' => 'responsable_stagiaire',
        ]);
    }
}
