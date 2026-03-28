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
    Schema::create('reclamations', function (Blueprint $table) {
    $table->uuid('id')->primary(); // Ton format REC-2026-001 sera géré par un helper ou stocké ici
    $table->foreignUuid('stagiaire_id')->constrained('stagiaire_profiles')->cascadeOnDelete();

    $table->string('type'); // Ex: Réclamation pédagogique, Question administrative
    $table->text('message');
    $table->enum('statut', ['En cours', 'Résolu', 'Fermé'])->default('En cours');

    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamations');
    }
};
