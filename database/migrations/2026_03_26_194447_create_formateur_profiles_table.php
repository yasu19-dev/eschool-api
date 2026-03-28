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
        Schema::create('formateur_profiles', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
        $table->string('matricule', 50)->unique(); // Ex: M12345
        $table->string('nom', 100);
        $table->string('prenom', 100);
        $table->string('email_professionnel', 150);
        $table->string('telephone', 20)->nullable();
        $table->string('adresse')->nullable();
        $table->text('bio')->nullable(); // Nouveau champ trouvé dans ton Profile.jsx
        $table->string('photo_url')->nullable();
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formateur_profiles');
    }
};
