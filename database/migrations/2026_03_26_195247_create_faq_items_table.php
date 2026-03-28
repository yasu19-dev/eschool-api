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
        Schema::create('faq_items', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('faq_categorie_id')->constrained()->cascadeOnDelete();
        $table->string('question');
        $table->text('reponse');
        $table->boolean('actif')->default(true);
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_items');
    }
};
