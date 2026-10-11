<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trace les appels dans le fil de discussion : un message participe au fil
 * avec `appel_id` (affiché comme un événement d'appel), et l'appel mémorise
 * l'instant de décrochage ainsi que la durée pour l'affichage.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('messages') && ! Schema::hasColumn('messages', 'appel_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->unsignedBigInteger('appel_id')->nullable();
            });
        }

        if (Schema::hasTable('appels')) {
            Schema::table('appels', function (Blueprint $table) {
                if (! Schema::hasColumn('appels', 'repond_le')) {
                    $table->timestamp('repond_le')->nullable();
                }
                if (! Schema::hasColumn('appels', 'duree')) {
                    $table->unsignedInteger('duree')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('messages') && Schema::hasColumn('messages', 'appel_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('appel_id');
            });
        }

        if (Schema::hasTable('appels')) {
            Schema::table('appels', function (Blueprint $table) {
                foreach (['repond_le', 'duree'] as $colonne) {
                    if (Schema::hasColumn('appels', $colonne)) {
                        $table->dropColumn($colonne);
                    }
                }
            });
        }
    }
};
