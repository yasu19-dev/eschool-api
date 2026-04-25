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
    Schema::table('modules', function (Blueprint $table) {
        // On ajoute la colonne formateur_id en tant qu'UUID nullable
        $table->foreignUuid('formateur_id')->nullable()->constrained('formateur_profiles')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('modules', function (Blueprint $table) {
        $table->dropForeign(['formateur_id']);
        $table->dropColumn('formateur_id');
    });
}
};
