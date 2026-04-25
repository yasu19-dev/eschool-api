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
        // (Utile pour l'admin, même si le formateur ne le voit plus)
Schema::create('justificatifs', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('stagiaire_id')->constrained('stagiaire_profiles')->cascadeOnDelete();
    $table->foreignUuid('seance_id')->constrained('seances')->cascadeOnDelete();
    $table->string('fichier_url');
    $table->enum('statut', ['En attente', 'Justifié', 'Non justifié'])->default('En attente');
    $table->boolean('est_valide')->default(false);
    $table->enum('type', ['Certificat Médical', 'Convocation', 'Autre'])->default('');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('justificatifs');
    }
};
