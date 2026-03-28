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
    Schema::create('demande_attestations', function (Blueprint $table) {
    $table->uuid('id')->primary(); // Format ATT-2025-XXX géré au niveau du code
    $table->foreignUuid('stagiaire_id')->constrained('stagiaire_profiles')->cascadeOnDelete();
    $table->string('type'); // Scolarité, Stage, Notes

    // Statuts exacts de ton AdminAttestations.jsx
    $table->enum('status', [
        'En attente',
        'Validée',
        'Prête pour récupération',
        'Livrée',
        'Refusée'
    ])->default('En attente');

    $table->text('motif_refus')->nullable();
    $table->timestamp('date_livraison_prevue')->nullable(); // Pourra être un jeudi/vendredi
    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_attestations');
    }
};
