<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pièces jointes des messages (images et documents) : chemin stocké sur le
 * disque « public » + nom d'origine du fichier, pour l'affichage et le
 * téléchargement.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messages')) {
            return;
        }

        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'piece_jointe')) {
                $table->string('piece_jointe')->nullable();
            }
            if (! Schema::hasColumn('messages', 'piece_jointe_nom')) {
                $table->string('piece_jointe_nom')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('messages')) {
            return;
        }

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['piece_jointe', 'piece_jointe_nom']);
        });
    }
};
