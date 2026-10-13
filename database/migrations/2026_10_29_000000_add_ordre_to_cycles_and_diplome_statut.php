<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fin de cycle et diplômation.
 *
 * - `cycles.ordre` donne la séquence d'enseignement (Maternelle → Primaire →
 *   Éducation de base → Humanités…). Une classe terminale dont le cycle n'est
 *   pas le dernier mène au 1er niveau du cycle suivant ; sur le DERNIER cycle,
 *   les admis sont diplômés.
 * - `eleves.statut` accueille la valeur « diplome » pour les sortants.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cycles') && ! Schema::hasColumn('cycles', 'ordre')) {
            Schema::table('cycles', function (Blueprint $table) {
                // Rang du cycle dans le parcours (1 = premier). NULL = non ordonné.
                $table->unsignedInteger('ordre')->nullable()->after('nom');
            });
        }

        if (Schema::hasTable('eleves') && Schema::hasColumn('eleves', 'statut')) {
            Schema::table('eleves', function (Blueprint $table) {
                $table->enum('statut', ['actif', 'inactif', 'suspendu', 'exclu', 'diplome'])
                    ->default('actif')
                    ->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('eleves') && Schema::hasColumn('eleves', 'statut')) {
            Schema::table('eleves', function (Blueprint $table) {
                $table->enum('statut', ['actif', 'inactif', 'suspendu', 'exclu'])
                    ->default('actif')
                    ->change();
            });
        }

        if (Schema::hasTable('cycles') && Schema::hasColumn('cycles', 'ordre')) {
            Schema::table('cycles', function (Blueprint $table) {
                $table->dropColumn('ordre');
            });
        }
    }
};
