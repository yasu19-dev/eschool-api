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
    Schema::create('notes', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('stagiaire_id')->constrained('stagiaire_profiles')->cascadeOnDelete();
    $table->foreignUuid('module_id')->constrained('modules')->cascadeOnDelete();
    $table->foreignUuid('formateur_id')->constrained('formateur_profiles')->cascadeOnDelete();
    $table->decimal('valeur', 5, 2)->nullable(); // Pour gérer les 16.50/20
    $table->enum('type_evaluation', ['cc1', 'cc2', 'efm'])->default('cc1');
    $table->string('session')->default('Normale');
    $table->timestamps();
});

}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
