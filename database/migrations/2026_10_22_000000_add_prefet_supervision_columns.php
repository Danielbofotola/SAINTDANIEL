<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Privilèges de supervision du préfet :
 *  - statut disciplinaire de l'élève (actif, suspendu, exclu) ;
 *  - validation des évaluations programmées par les enseignants ;
 *  - approbation des bulletins.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('eleves', 'statut')) {
            Schema::table('eleves', function (Blueprint $table) {
                $table->enum('statut', ['actif', 'inactif', 'suspendu', 'exclu'])->default('actif')->change();
            });
        }

        if (Schema::hasTable('evaluations') && ! Schema::hasColumn('evaluations', 'validee_at')) {
            Schema::table('evaluations', function (Blueprint $table) {
                $table->timestamp('validee_at')->nullable()->after('statut');
                $table->unsignedBigInteger('validee_par')->nullable()->after('validee_at');
            });
        }

        if (Schema::hasTable('bulletins') && ! Schema::hasColumn('bulletins', 'approuve_at')) {
            Schema::table('bulletins', function (Blueprint $table) {
                $table->timestamp('approuve_at')->nullable()->after('statut');
                $table->unsignedBigInteger('approuve_par')->nullable()->after('approuve_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('eleves', 'statut')) {
            Schema::table('eleves', function (Blueprint $table) {
                $table->enum('statut', ['actif', 'inactif'])->default('actif')->change();
            });
        }

        foreach (['evaluations' => ['validee_at', 'validee_par'], 'bulletins' => ['approuve_at', 'approuve_par']] as $table => $colonnes) {
            foreach ($colonnes as $colonne) {
                if (Schema::hasColumn($table, $colonne)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropColumn($colonne));
                }
            }
        }
    }
};
