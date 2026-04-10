<?php

namespace Database\Seeders;

use App\Models\FormateurProfile;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\Seance;
use App\Models\StagiaireProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // AdminSeeder::class,
            // PedagogiqueSeeder::class,
            // StagiaireSeeder::class,
            // SeanceAbsenceSeeder::class,
            // FaqSeeder::class,
            FiliereSeeder::class,
            ModuleSeeder::class,
        ]);

    }
}

