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
    Schema::create('presences', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('seance_id')->constrained('seances')->cascadeOnDelete();
    $table->foreignUuid('stagiaire_id')->constrained('stagiaire_profiles');
   $table->boolean('est_absent')->default(false);
        $table->boolean('est_en_retard')->default(false);
        $table->boolean('est_justifie')->default(false);
    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
