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
        Schema::create('modules', function (Blueprint $table) {
        $table->uuid('id')->primary(); // C'est un UUID, on s'en souvient pour le modèle !
        $table->string('code')->unique();
        $table->string('intitule'); // On garde 'intitule'
        $table->integer('coefficient')->default(1);


        $table->integer('masse_horaire')->default(0);

        $table->timestamps();
  
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
