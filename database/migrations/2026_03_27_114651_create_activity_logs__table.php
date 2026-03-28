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
       Schema::create('activity_logs', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('action'); // Ex: 'Validation', 'Import', 'Connexion'
    $table->string('description'); // Ex: 'Attestation validée pour Ahmed Bennani'
    $table->string('icon')->nullable(); // Pour stocker 'Users', 'Clock', etc.
    $table->string('color')->nullable(); // Pour stocker le code couleur hexadécimal
    $table->timestamps(); // Gère le "Il y a 10 min"
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs_');
    }
};
