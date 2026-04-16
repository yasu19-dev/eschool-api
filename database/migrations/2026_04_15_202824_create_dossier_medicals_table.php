<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers_medicaux', function (Blueprint $table) {
            $table->id();
            // Lien avec le profil du stagiaire (ajuste le nom de la table si besoin)
            $table->foreignUuid('stagiaire_id')->constrained('stagiaire_profiles')->cascadeOnDelete();

            $table->string('code_stagiaire', 50);
            $table->string('cin', 20);
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('adresse');
            $table->string('ville', 100);
            $table->string('telephone', 20);
            $table->string('situation_familiale', 50);
            $table->boolean('allocation_familiale')->default(false);

            $table->enum('statut', ['En attente', 'Traitée', 'Rejetée'])->default('En attente');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers_medicaux');
    }
};
