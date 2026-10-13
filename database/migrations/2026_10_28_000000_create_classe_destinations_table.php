<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Destinations multiples d'une classe en fin d'année.
 *
 * La classe qui clôt un cycle (ex. dernière année de l'éducation de base) peut
 * mener vers PLUSIEURS classes d'accueil — typiquement les 1res humanités, une
 * par section/option. Le passage devient alors un choix par élève au moment de
 * la délibération (cf. DeliberationController).
 *
 * Une classe sans destination explicite conserve le comportement historique :
 * la classe de niveau immédiatement supérieur, ou `classe_suivante_id`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('classe_destinations')) {
            Schema::create('classe_destinations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
                $table->foreignId('destination_id')->constrained('classes')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['classe_id', 'destination_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('classe_destinations');
    }
};
