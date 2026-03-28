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

    // Clés étrangères (Relations)
    // On garde le nullOnDelete pour le formateur au cas où un compte est supprimé
    // mais on veut garder l'historique des cours.
    $table->foreignUuid('formateur_id')->nullable()->constrained('formateur_profiles')->nullOnDelete();
    $table->foreignUuid('module_id')->constrained('modules')->cascadeOnDelete();
    $table->foreignUuid('groupe_id')->constrained('groupes')->cascadeOnDelete();

    // Informations de la séance
    $table->date('date');

    // On utilise 'creneau' au lieu de heure_debut/fin pour correspondre
    // exactement au "timeSlot" de tes fichiers React (ex: "08:30-10:50")
    $table->string('creneau');

    $table->string('salle', 50)->nullable();

    // Type de séance (utile pour ton Dashboard Formateur)
    $table->enum('type', ['Cours', 'Controle Continu', 'EFM', 'TP'])->default('Cours');

    // Ce champ remplace le "motif_global" que tu avais dans absence_sessions
    // Il permet au prof de laisser une note sur le déroulement du cours
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
