<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dates des périodes : elles permettent de déterminer automatiquement la
 * période « en cours » au moment d'un paiement (celle qui contient la date du
 * jour). Laissées vides, la première période active sert de repli.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('periodes')) {
            Schema::table('periodes', function (Blueprint $table) {
                if (! Schema::hasColumn('periodes', 'date_debut')) {
                    $table->date('date_debut')->nullable()->after('nom');
                }
                if (! Schema::hasColumn('periodes', 'date_fin')) {
                    $table->date('date_fin')->nullable()->after('date_debut');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('periodes')) {
            Schema::table('periodes', function (Blueprint $table) {
                foreach (['date_debut', 'date_fin'] as $colonne) {
                    if (Schema::hasColumn('periodes', $colonne)) {
                        $table->dropColumn($colonne);
                    }
                }
            });
        }
    }
};
