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
    Schema::table('notes', function (Blueprint $table) {
        // On ajoute le semestre (S1 ou S2) après la colonne session
        $table->enum('semestre', ['S1', 'S2'])->default('S1')->after('session');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            //
        });
    }
};
