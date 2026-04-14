<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Création de la table 'settings'
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // La 'key' sera l'identifiant technique (ex: 'absence_limit')
            $table->string('key')->unique();
            // La 'value' stockera la donnée (ex: '15')
            $table->text('value')->nullable();
            // Le 'group' permet de classer par onglet (general, academic, etc.)
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // INSERTION DES DONNÉES PAR DÉFAUT
        // Cela permet d'avoir des valeurs dès le premier chargement
        DB::table('settings')->insert([
            // Onglet Général
            ['key' => 'institution_name', 'value' => 'ISMONTIC', 'group' => 'general'],
            ['key' => 'institution_email', 'value' => 'contact@ismontic.ma', 'group' => 'general'],
            ['key' => 'institution_phone', 'value' => '+212 5 22 XX XX XX', 'group' => 'general'],

            // Onglet Académique
            ['key' => 'absence_limit', 'value' => '15', 'group' => 'academic'],
            ['key' => 'passing_grade', 'value' => '10', 'group' => 'academic'],
            ['key' => 'current_year', 'value' => '2025-2026', 'group' => 'academic'],

            // Onglet Sécurité
            ['key' => 'session_timeout', 'value' => '30', 'group' => 'security'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
