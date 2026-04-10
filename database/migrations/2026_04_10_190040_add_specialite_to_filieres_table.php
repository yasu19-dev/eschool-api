<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('filieres', function (Blueprint $table) {
            // ✅ Ajout de la colonne après 'title'
            $table->string('specialite')->nullable()->after('title');
        });
    }

    public function down(): void {
        Schema::table('filieres', function (Blueprint $table) {
            $table->dropColumn('specialite');
        });
    }
};
