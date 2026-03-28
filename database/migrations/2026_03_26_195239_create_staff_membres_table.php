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
        Schema::create('staff_membres', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('name');
        $table->string('role');
        $table->string('email');
        $table->string('initials', 5); // Ex: 'AM'
        $table->enum('category', ['direction', 'administration', 'pedagogique'])->default('pedagogique');
        $table->integer('order')->default(0);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_membres');
    }
};
