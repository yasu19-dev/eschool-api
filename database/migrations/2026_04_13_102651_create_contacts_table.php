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
        Schema::create('contacts', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('nom');
    $table->string('email');
    $table->string('telephone')->nullable();
    $table->string('sujet');
    $table->text('message');
    $table->boolean('lu')->default(false); // Pour que le responsable puisse trier
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
