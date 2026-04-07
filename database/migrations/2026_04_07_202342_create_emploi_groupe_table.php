<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('emploi_groupe', function (Blueprint $table) {
        $table->id();

        // Clé étrangère vers la table emplois
        $table->foreignId('emploi_id')
              ->constrained()
              ->onDelete('cascade');

        // Clé étrangère vers la table groupes
       $table->uuid('group_id');
$table->foreign('group_id')->references('id')->on('groupes')->onDelete('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emploi_groupe');
    }
};
