<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Devises et taux de change : chaque ligne de frais porte sa devise (USD par
 * défaut), chaque paiement retient la devise encaissée et le taux appliqué, et
 * l'établissement fixe un taux de change CDF/USD par défaut.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('frais_scolaires') && ! Schema::hasColumn('frais_scolaires', 'devise')) {
            Schema::table('frais_scolaires', function (Blueprint $table) {
                $table->string('devise', 3)->default('USD')->after('montant_exige');
            });
        }

        if (Schema::hasTable('paiements')) {
            Schema::table('paiements', function (Blueprint $table) {
                if (! Schema::hasColumn('paiements', 'devise')) {
                    $table->string('devise', 3)->default('USD')->after('montant_paye');
                }
                if (! Schema::hasColumn('paiements', 'taux')) {
                    $table->decimal('taux', 12, 2)->nullable()->after('devise');
                }
                if (! Schema::hasColumn('paiements', 'montant_recu')) {
                    $table->decimal('montant_recu', 12, 2)->nullable()->after('taux');
                }
            });
        }

        if (Schema::hasTable('parametres_systeme')) {
            Schema::table('parametres_systeme', function (Blueprint $table) {
                if (! Schema::hasColumn('parametres_systeme', 'devise')) {
                    $table->string('devise', 3)->default('USD');
                }
                if (! Schema::hasColumn('parametres_systeme', 'taux_change')) {
                    $table->decimal('taux_change', 12, 2)->default(2800);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('frais_scolaires') && Schema::hasColumn('frais_scolaires', 'devise')) {
            Schema::table('frais_scolaires', fn (Blueprint $table) => $table->dropColumn('devise'));
        }

        if (Schema::hasTable('paiements')) {
            Schema::table('paiements', function (Blueprint $table) {
                foreach (['devise', 'taux', 'montant_recu'] as $colonne) {
                    if (Schema::hasColumn('paiements', $colonne)) {
                        $table->dropColumn($colonne);
                    }
                }
            });
        }

        if (Schema::hasTable('parametres_systeme')) {
            Schema::table('parametres_systeme', function (Blueprint $table) {
                foreach (['devise', 'taux_change'] as $colonne) {
                    if (Schema::hasColumn('parametres_systeme', $colonne)) {
                        $table->dropColumn($colonne);
                    }
                }
            });
        }
    }
};
