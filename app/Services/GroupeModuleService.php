<?php

namespace App\Services;

use App\Models\Groupe;
use App\Models\Filiere;
use App\Models\Module;

class GroupeModuleService
{
    public function syncModules(Groupe $groupe)
    {
        $groupCode = $groupe->code;
        $filiereCode = $this->detectFiliereCode($groupCode);

        $filiere = Filiere::where('code', $filiereCode)->first();

        if ($filiere) {
            // ✅ SÉCURITÉ : On vérifie si modules est une chaîne de caractères (string)
            // Si c'est le cas, on le décode manuellement pour obtenir un tableau (array)
            $modulesList = $filiere->modules;

            if (is_string($modulesList)) {
                $modulesList = json_decode($modulesList, true);
            }

            // On vérifie que c'est bien un tableau et qu'il n'est pas vide
            if (is_array($modulesList) && !empty($modulesList)) {
                // On récupère les IDs des modules listés
                $moduleIds = Module::whereIn('code', $modulesList)->pluck('id');

                // On remplit la table pivot groupe_module
                $groupe->modules()->sync($moduleIds);
            }
        }
    }

    private function detectFiliereCode($groupCode)
    {
        $groupCode = strtoupper($groupCode);

        // --- Année 1 (Tronc Commun) ---
        if (str_contains($groupCode, '10')) {
            if (str_starts_with($groupCode, 'DEV')) return 'DEV';
            if (str_starts_with($groupCode, 'INFO')) return 'INFO';
            if (str_starts_with($groupCode, 'ID')) return 'ID';
            if (str_starts_with($groupCode, 'AI')) return 'AI';
        }

        // --- Année 2 (Spécialités) ---
        if (str_contains($groupCode, '20')) {
            if (str_contains($groupCode, 'WOFS')) return 'DEVFS'; // Fullstack
            if (str_contains($groupCode, 'IDCC')) return 'IDCC'; // Cloud Computing
            if (str_contains($groupCode, 'IDCS')) return 'IDCS'; // Cyber sécurité
            if (str_contains($groupCode, 'IDRS')) return 'IDRS'; // Systèmes et Réseaux
            if (str_contains($groupCode, 'AIOA')) return 'AIOA'; // AI Data Analyst
            if (str_starts_with($groupCode, 'INFO')) return 'INFO2';
        }

        return null;
    }
}
