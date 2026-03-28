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
    Schema::create('filieres', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
        $table->string('title');
        $table->string('code', 20)->unique();
        $table->string('duration')->default('2 ans');
        $table->string('niveau'); // Ex: 'Technicien Spécialisé'
        $table->text('description');
        $table->json('modules'); // On stocke la liste en JSON pour React
        $table->json('debouches'); // On stocke la liste en JSON pour React
        $table->string('color')->default('#1E88E5');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filieres');
    }
};
