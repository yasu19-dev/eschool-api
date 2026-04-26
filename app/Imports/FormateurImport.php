<?php

namespace App\Imports;

use App\Models\User;
use App\Models\FormateurProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FormateurImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // 1. Création ou mise à jour de l'utilisateur
                // On utilise updateOrCreate pour éviter les doublons si on ré-importe le fichier
                $user = User::updateOrCreate(
                    ['email' => trim($row['email'])],
                    [
                        'id' => (string) Str::uuid(),
                        'password' => Hash::make($row['password'] ?? 'Ismontic2026'),
                        'role' => 'formateur',
                        'etat' => 'Actif',
                    ]
                );

                // 2. Création ou mise à jour du profil formateur lié
                FormateurProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'id' => (string) Str::uuid(),
                        'matricule' => trim($row['matricule']),
                        'nom' => strtoupper(trim($row['nom'])),
                        'prenom' => ucfirst(strtolower(trim($row['prenom']))),
                        'email_professionnel' => $user->email,
                    ]
                );
            }
        });
    }
}
