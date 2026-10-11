<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Classe suivante explicite : rend le passage configurable d'une classe à
 * l'autre, y compris entre deux cycles (ex. 6e primaire → 1re secondaire), ce
 * que l'ordre interne au cycle ne pouvait pas exprimer. Vide = on retombe sur
 * l'ordre immédiatement supérieur, dans le même cycle.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('classes') && ! Schema::hasColumn('classes', 'classe_suivante_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->unsignedBigInteger('classe_suivante_id')->nullable()->after('ordre');
                $table->foreign('classe_suivante_id')->references('id')->on('classes')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('classes') && Schema::hasColumn('classes', 'classe_suivante_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropForeign(['classe_suivante_id']);
                $table->dropColumn('classe_suivante_id');
            });
        }
    }
};
