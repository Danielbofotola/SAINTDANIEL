<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fin de cycle configurable : chaque cycle peut délivrer une certification en
 * fin de parcours (ex. « Certificat d'études primaires ») ou un diplôme
 * (« Diplôme d'État »), ou simplement laisser passer au cycle suivant.
 *
 * - `fin_type` : aucune | certificat | diplome
 * - `fin_libelle` : l'intitulé remis (facultatif, ex. « Certificat d'études primaires »).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cycles') && ! Schema::hasColumn('cycles', 'fin_type')) {
            Schema::table('cycles', function (Blueprint $table) {
                $table->string('fin_type', 20)->default('aucune')->after('ordre');
            });
        }

        if (Schema::hasTable('cycles') && ! Schema::hasColumn('cycles', 'fin_libelle')) {
            Schema::table('cycles', function (Blueprint $table) {
                $table->string('fin_libelle', 100)->nullable()->after('fin_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cycles')) {
            Schema::table('cycles', function (Blueprint $table) {
                if (Schema::hasColumn('cycles', 'fin_libelle')) {
                    $table->dropColumn('fin_libelle');
                }
                if (Schema::hasColumn('cycles', 'fin_type')) {
                    $table->dropColumn('fin_type');
                }
            });
        }
    }
};
