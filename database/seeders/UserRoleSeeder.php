<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\AdminProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CRÉATION DES RÔLES DANS LA TABLE 'roles'
        $roleAdmin = Role::firstOrCreate(['code' => 'admin']);
        $roleFormateur = Role::firstOrCreate(['code' => 'formateur']);
        $roleStagiaire = Role::firstOrCreate(['code' => 'stagiaire']);

        $password = Hash::make('password123');

        // 2. CRÉATION DE L'UTILISATEUR (Sans la colonne role !)
        $adminUser = User::create([
            'email' => 'admin@ismontic.ma',
            'password' => $password,
        ]);

        // 3. ATTACHER LE RÔLE VIA LA TABLE PIVOT 'role_user'
        $adminUser->roles()->attach($roleAdmin->id);

        // 4. CRÉATION DU PROFIL
        AdminProfile::create([
            'user_id' => $adminUser->id,
            'nom' => 'ISMONTIC',
            'prenom' => 'Admin',
        ]);

        
    }
}
