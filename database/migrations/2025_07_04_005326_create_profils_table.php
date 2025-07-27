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
    Schema::create('profils', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->unique();
        $table->string('phone')->nullable();
        $table->string('adresse')->nullable();
        $table->string('ville')->nullable();
        $table->string('quartier')->nullable();
        $table->string('photo')->nullable();
        $table->string('cv')->nullable();
        $table->string('portfolio')->nullable();
        $table->text('competences')->nullable();
        $table->text('experiences')->nullable();
        $table->string('video')->nullable();
        $table->json('reseaux')->nullable();
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profils');
    }
};
