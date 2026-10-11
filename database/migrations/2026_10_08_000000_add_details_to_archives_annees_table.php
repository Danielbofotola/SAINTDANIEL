<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Une clôture d'année ne se limitait qu'aux recettes/dépenses/solde :
     * on y ajoute ce qui manquait (frais attendus, impayés, résultats,
     * effectifs par sexe) pour que « tout » soit figé au moment de la clôture.
     */
    public function up(): void
    {
        Schema::table('archives_annees', function (Blueprint $table) {
            if (! Schema::hasColumn('archives_annees', 'total_attendu')) {
                $table->decimal('total_attendu', 12, 2)->default(0)->after('solde_final');
            }
            if (! Schema::hasColumn('archives_annees', 'total_impayes')) {
                $table->decimal('total_impayes', 12, 2)->default(0)->after('total_attendu');
            }
            if (! Schema::hasColumn('archives_annees', 'eleves_garcons')) {
                $table->unsignedInteger('eleves_garcons')->default(0)->after('total_impayes');
            }
            if (! Schema::hasColumn('archives_annees', 'eleves_filles')) {
                $table->unsignedInteger('eleves_filles')->default(0)->after('eleves_garcons');
            }
            if (! Schema::hasColumn('archives_annees', 'total_bulletins')) {
                $table->unsignedInteger('total_bulletins')->default(0)->after('eleves_filles');
            }
            if (! Schema::hasColumn('archives_annees', 'total_notes')) {
                $table->unsignedInteger('total_notes')->default(0)->after('total_bulletins');
            }
        });
    }

    public function down(): void
    {
        Schema::table('archives_annees', function (Blueprint $table) {
            $table->dropColumn(['total_attendu', 'total_impayes', 'eleves_garcons', 'eleves_filles', 'total_bulletins', 'total_notes']);
        });
    }
};
