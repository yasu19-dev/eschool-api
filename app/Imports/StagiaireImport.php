<?php

namespace App\Imports;

use App\Models\User;
use App\Models\StagiaireProfile;
use App\Models\Groupe;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StagiaireImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // 1. On cherche l'ID du groupe par son code (ex: 'DEV-101')
                // Dans StagiaireImport.php
            $groupe = Groupe::where('code', trim(strtoupper($row['groupe'])))->first();

                // 2. Création de l'utilisateur
                $user = User::create([
                    'email'    => $row['email'],
                    'password' => Hash::make($row['password'] ?? 'Ismontic2026'), // Mot de passe par défaut
                    'role'     => 'stagiaire',
                    'etat'     => 'Actif',
                ]);

                // 3. Création du profil stagiaire lié
                StagiaireProfile::create([
                    'user_id'         => $user->id,
                    'nom'             => $row['nom'],
                    'prenom'          => $row['prenom'],
                    'cef'             => $row['cef'],
                    'groupe_id'       => $groupe ? $groupe->id : null,
                    'annee_scolaire'  => $row['annee'] ?? '2025/2026',
                ]);
            }
        });
    }
}
