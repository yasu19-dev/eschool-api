<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
<<<<<<<< HEAD:database/migrations/2026_04_10_162756_add_cc3_to_type_evaluation_in_notes_table.php
    public function up(): void
{
    Schema::table('notes', function (Blueprint $table) {
        $table->enum('type_evaluation', ['cc1', 'cc2', 'cc3', 'efm'])->default('cc1')->change();
========
   public function up(): void
{
    Schema::create('groupe-seance', function (Blueprint $table) {
        $table->id();
        $table->foreignUuid('seance_id')->constrained('seances')->references('id')->onDelete('cascade');
        $table->timestamps();
>>>>>>>> 3dab9fa8480a172fa11f37c635e4e1c061dc06b7:database/migrations/2026_04_09_163744_create_groupe-seance_table.php
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<<< HEAD:database/migrations/2026_04_10_162756_add_cc3_to_type_evaluation_in_notes_table.php
        Schema::table('notes', function (Blueprint $table) {
            //
        });
========
        Schema::dropIfExists('groupe-seance');
>>>>>>>> 3dab9fa8480a172fa11f37c635e4e1c061dc06b7:database/migrations/2026_04_09_163744_create_groupe-seance_table.php
    }
};
