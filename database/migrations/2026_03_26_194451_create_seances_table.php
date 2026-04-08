<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seances', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // --- RELATIONS (Clés étrangères) ---
            // On lie la séance au profil du formateur
            $table->foreignUuid('formateur_id')
                  ->nullable()
                  ->constrained('formateur_profiles')
                  ->nullOnDelete();

            // On lie la séance au module et au groupe
            $table->foreignUuid('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignUuid('groupe_id')->constrained('groupes')->cascadeOnDelete();

            // --- PLANIFICATION ---
            // La date réelle du cours (ex: 2026-04-06)
            $table->date('date');

            // Le créneau horaire (ex: "08:30-10:30" ou "14:30-18:30")
            $table->string('creneau');

            // La salle (ex: "SDD1", "SL2").
            // Si vide ou "A DISTANCE", le modèle calculera "distanciel"
            $table->string('salle', 50)->nullable();

            // --- NOTES ET SUIVI ---
            // Permet au formateur de laisser un commentaire sur la séance
            $table->text('commentaire_prof')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seances');
    }
};
