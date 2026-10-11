<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La colonne `statut` de `inscriptions` était un ENUM limité à
     * ('actif','inactif') : « en_attente » (inscription en attente du frais
     * d'inscription) provoquait « Data truncated for column 'statut' ».
     */
    public function up(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->enum('statut', ['actif', 'inactif', 'en_attente'])->default('actif')->change();
        });
    }

    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->enum('statut', ['actif', 'inactif'])->default('actif')->change();
        });
    }
};
