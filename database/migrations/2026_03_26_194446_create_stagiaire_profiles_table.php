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
        $table->string('cne', 50)->unique(); // Code étudiant
        $table->string('nom', 100);
        $table->string('prenom', 100);
        $table->string('email_institutionnel', 150)->nullable();
        $table->string('telephone', 20)->nullable();
        $table->string('adresse')->nullable();

        // --- Nouveaux champs Couverture Médicale / Allocations ---
        $table->enum('situation_familiale', ['Célibataire', 'Marié', 'Divorcé', 'Veuf'])->default('Célibataire');
        $table->boolean('beneficie_allocation')->default(false);
        $table->string('num_affiliation_cnss')->nullable(); // Optionnel selon le besoin

        $table->enum('statut', ['Actif', 'Suspendu', 'Diplome'])->default('Actif');
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
