<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réponse citée (le message auquel on répond) et réaction émoji d'un message.
 *
 * Colonne simple, sans contrainte de clé étrangère : SQLite ne sait pas
 * ajouter une clé étrangère par ALTER TABLE, et une réponse à un message
 * supprimé se contente d'un pointeur orphelin.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messages')) {
            return;
        }

        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'reponse_a_id')) {
                $table->unsignedBigInteger('reponse_a_id')->nullable();
            }
            if (! Schema::hasColumn('messages', 'reaction')) {
                $table->string('reaction', 16)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('messages')) {
            return;
        }

        Schema::table('messages', function (Blueprint $table) {
            foreach (['reponse_a_id', 'reaction'] as $colonne) {
                if (Schema::hasColumn('messages', $colonne)) {
                    $table->dropColumn($colonne);
                }
            }
        });
    }
};
