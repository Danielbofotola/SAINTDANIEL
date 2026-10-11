<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modules optionnels de la caisse : la page « reçu » (impression) et la page
 * « recouvrement » peuvent être activées ou désactivées depuis « Commuter les
 * modules ». Actives par défaut.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('parametres_systeme')) {
            Schema::table('parametres_systeme', function (Blueprint $table) {
                if (! Schema::hasColumn('parametres_systeme', 'module_recus_actif')) {
                    $table->boolean('module_recus_actif')->default(true);
                }
                if (! Schema::hasColumn('parametres_systeme', 'module_recouvrement_actif')) {
                    $table->boolean('module_recouvrement_actif')->default(true);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('parametres_systeme')) {
            Schema::table('parametres_systeme', function (Blueprint $table) {
                foreach (['module_recus_actif', 'module_recouvrement_actif'] as $colonne) {
                    if (Schema::hasColumn('parametres_systeme', $colonne)) {
                        $table->dropColumn($colonne);
                    }
                }
            });
        }
    }
};
