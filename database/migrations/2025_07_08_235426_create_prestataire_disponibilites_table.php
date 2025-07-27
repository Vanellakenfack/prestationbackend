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
        Schema::create('prestataire_disponibilites', function (Blueprint $table) {
    $table->id();
    $table->foreignId('prestataire_id')->constrained('prestataires')->cascadeOnDelete(); // Changé 'users' en 'prestataires'
    $table->date('jour');
    $table->time('heure_debut')->default('08:00:00');
    $table->time('heure_fin')->default('18:00:00');
    $table->boolean('est_disponible')->default(true);
    $table->timestamps();
    
    $table->unique(['prestataire_id', 'jour']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestataire_disponibilites');
    }
};
