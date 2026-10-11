<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suppression « pour soi » : chaque participant peut retirer une discussion de
 * sa liste sans que les autres perdent les messages. La colonne est remise à
 * null dès qu'un nouveau message est publié, si bien que rien n'est définitif :
 * la discussion réapparaît pour celui qui l'avait retirée.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('conversation_user') && ! Schema::hasColumn('conversation_user', 'supprime_at')) {
            Schema::table('conversation_user', function (Blueprint $table) {
                $table->timestamp('supprime_at')->nullable()->after('dernier_message_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('conversation_user', 'supprime_at')) {
            Schema::table('conversation_user', function (Blueprint $table) {
                $table->dropColumn('supprime_at');
            });
        }
    }
};
