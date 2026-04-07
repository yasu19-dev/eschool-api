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
    Schema::create('emplois_du_temps_pdf', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('groupe_id')->constrained('groupes')->cascadeOnDelete();
        $table->string('titre');
        $table->string('fichier_url');
        $table->string('format');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emploi_du_temps_pdfs');
    }
};
