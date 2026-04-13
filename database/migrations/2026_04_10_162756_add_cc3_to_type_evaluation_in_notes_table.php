<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void{
    Schema::table('notes', function (Blueprint $table) {
        $table->enum('type_evaluation', ['cc1', 'cc2', 'cc3', 'efm'])->default('cc1')->change();
        }
    );}

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
