<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->text('description');
            $table->string('categorie');
            $table->decimal('prix', 10, 2);
            $table->string('unite_prix')->default('heure'); // heure, forfait, etc.
            $table->string('localisation');
            $table->boolean('disponible')->default(true);
            $table->json('photos')->nullable(); // Pour stocker plusieurs photos
            $table->string('video')->nullable(); // Lien vers une vidéo de présentation
            $table->timestamps();
            
            $table->index('categorie');
            $table->index('localisation');
        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
};