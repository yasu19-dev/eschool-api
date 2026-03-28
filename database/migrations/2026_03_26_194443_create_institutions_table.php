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
        Schema::create('institutions', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('nom')->default('ISMONTIC');
        $table->string('email')->default('contact@ismontic.ma');
        $table->string('telephone')->nullable();
        $table->string('adresse')->nullable();
        $table->string('site_web')->nullable();
        $table->text('description')->nullable();
        


        // Paramètres système (Maintenance, Backup) trouvés dans ton code
        $table->boolean('maintenance_mode')->default(false);
        $table->timestamp('last_backup_at')->nullable();
        $table->string('system_version')->default('2.0.0');

        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
