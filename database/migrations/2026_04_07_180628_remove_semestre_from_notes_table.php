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
    Schema::table('notes', function (Blueprint $table) {
        $table->dropColumn('semestre'); // Supprime la colonne définitivement
    });
}

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->string('semestre')->nullable(); // Pour pouvoir revenir en arrière si besoin
        });
    }
};
