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
        Schema::create('stagiaire_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            // Informations obligatoires du Cahier des Charges
            $table->string('cef', 20)->unique(); // Identifiant stagiaire
            $table->string('cin', 15)->unique()->nullable();
            $table->string('nom', 100);
            $table->string('prenom', 100);

            // Détails personnels
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance', 100)->nullable();
            $table->string('adresse')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('photo_url')->nullable();

            // Informations académiques
            $table->date('date_inscription')->nullable();
            $table->string('annee_scolaire', 20)->nullable(); // Ex: 2025/2026

            // Relation avec le groupe (Clé étrangère)
            $table->foreignUuid('groupe_id')->nullable()->constrained('groupes')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stagiaire_profiles');
    }
};
