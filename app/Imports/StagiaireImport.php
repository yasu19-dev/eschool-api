<?php

namespace App\Imports;

use App\Models\User;
use App\Models\StagiaireProfile;
use App\Models\Groupe;
use App\Services\GroupeModuleService; // ✅ Ajout du service
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StagiaireImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $syncService = new GroupeModuleService(); // ✅ Initialisation

        DB::transaction(function () use ($rows, $syncService) {
            foreach ($rows as $row) {
                // 1. Recherche ou création du groupe
                $groupeCode = trim(strtoupper($row['groupe']));
                $groupe = Groupe::where('code', $groupeCode)->first();

                // ✅ LOGIQUE DE SYNCHRONISATION : On lie les modules au groupe
                if ($groupe) {
                    $syncService->syncModules($groupe);
                }

                // 2. Création de l'utilisateur
                $user = User::create([
                    'email'    => $row['email'],
                    'password' => Hash::make($row['password'] ?? 'Ismontic2026'),
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
