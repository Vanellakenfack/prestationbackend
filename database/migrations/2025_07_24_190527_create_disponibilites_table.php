<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Première partie : Création de la table avec les colonnes
        Schema::create('disponibilites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained('users')->onDelete('cascade');
            $table->date('date'); // 'date' n'est plus unique ici
            $table->boolean('morning')->default(false);    // Disponibilité le matin
            $table->boolean('afternoon')->default(false); // Disponibilité l'après-midi
            $table->boolean('evening')->default(false);   // Disponibilité le soir
            $table->timestamps(); // created_at et updated_at
        });

        // Deuxième partie : Ajout de la contrainte unique composite après la création de la table
        // Cela peut contourner le problème d'analyse que vous rencontrez.
        Schema::table('disponibilites', function (Blueprint $table) {
            $table->unique(['prestataire_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disponibilites');
    }
};

