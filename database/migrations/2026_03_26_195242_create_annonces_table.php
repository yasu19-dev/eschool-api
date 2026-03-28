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
    Schema::create('annonces', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('formateur_id')->constrained('formateur_profiles')->cascadeOnDelete();
    $table->string('titre');
    $table->text('contenu');
    $table->enum('type', ['Examen', 'Absence Formateur', 'Information']); // Selon ton Select React
    $table->foreignUuid('groupe_id')->nullable()->constrained('groupes'); // Pour cibler une classe
    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};
