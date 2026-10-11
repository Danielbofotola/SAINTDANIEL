<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('parametres_systeme', 'saisie_notes_active')) {
            Schema::table('parametres_systeme', function (Blueprint $table) {
                $table->boolean('saisie_notes_active')->default(true);
            });
        }

        if (! Schema::hasColumn('parametres_systeme', 'portail_parents_actif')) {
            Schema::table('parametres_systeme', function (Blueprint $table) {
                $table->boolean('portail_parents_actif')->default(false);
            });
        }
    }

    public function down(): void
    {
        foreach (['saisie_notes_active', 'portail_parents_actif'] as $column) {
            if (Schema::hasColumn('parametres_systeme', $column)) {
                Schema::table('parametres_systeme', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
