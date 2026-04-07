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
        Schema::table('annonces', function (Blueprint $table) {
            // 1. On supprime l'ancienne colonne 'type'

            // 2. On ajoute la nouvelle colonne 'categorie'
            // On utilise string au lieu d'enum pour pouvoir ajouter
            // des catégories plus tard sans refaire de migration.
            $table->string('categorie')->default('Information')->after('titre');

            // 3. On ajoute une colonne 'priorite' (Optionnel mais recommandé pour le style)
            $table->enum('priorite', ['urgent', 'important', 'info'])->default('info')->after('categorie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            // On fait l'inverse en cas de rollback
            $table->dropColumn(['categorie', 'priorite']);
            $table->enum('type', ['Examen', 'Absence Formateur', 'Information'])->after('titre');
        });
    }
};
