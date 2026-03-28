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
        Schema::create('localisations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Lien avec l'institution (Clé étrangère)
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();

            // URL de la carte (Google Maps Iframe ou lien direct)
            // Comme vu dans ton StarUML (urlMap) et Contact.jsx
            $table->text('url_map')->nullable();

            // On peut ajouter ces champs optionnels pour plus de précision si besoin
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('localisations');
    }
};
