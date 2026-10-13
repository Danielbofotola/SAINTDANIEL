<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Autorisation donnée par l'administration au Préfet des Études de valider
 * (accepter / refuser) les demandes d'inscription. Décoché (défaut), seul
 * l'administrateur valide.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('parametres_systeme') && ! Schema::hasColumn('parametres_systeme', 'prefet_valide_inscriptions')) {
            Schema::table('parametres_systeme', function (Blueprint $table) {
                $table->boolean('prefet_valide_inscriptions')->default(false)->after('deliberation_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('parametres_systeme') && Schema::hasColumn('parametres_systeme', 'prefet_valide_inscriptions')) {
            Schema::table('parametres_systeme', function (Blueprint $table) {
                $table->dropColumn('prefet_valide_inscriptions');
            });
        }
    }
};
