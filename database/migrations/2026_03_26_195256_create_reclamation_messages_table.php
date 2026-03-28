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
    Schema::create('reclamation_messages', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('reclamation_id')->constrained('reclamations')->cascadeOnDelete();
        $table->foreignUuid('auteur_user_id')->constrained('users')->cascadeOnDelete();
        $table->text('message');
        $table->timestamps(); // Gère EnvoyeLe
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamation_messages');
    }
};
