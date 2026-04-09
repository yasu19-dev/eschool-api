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
    Schema::table('absences', function (Blueprint $table) {
        // On ajoute la colonne date
       $table->date('date')->nullable()->after('stagiaire_id');

        // Optionnel : On peut ajouter une contrainte d'unicité pour éviter les doublons
        // d'un même stagiaire pour une même séance le même jour.
        $table->unique(['seance_id', 'stagiaire_id', 'date']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            //
        });
    }
};
