<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le suivi « lu / non lu » se fait sur l'identifiant du dernier message lu :
 * plus fiable que l'horodatage, dont la précision est à la seconde (un message
 * envoyé dans la même seconde que la lecture était considéré comme lu).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('conversation_user') && ! Schema::hasColumn('conversation_user', 'dernier_message_id')) {
            Schema::table('conversation_user', function (Blueprint $table) {
                $table->unsignedBigInteger('dernier_message_id')->nullable()->after('dernier_lu_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('conversation_user', 'dernier_message_id')) {
            Schema::table('conversation_user', function (Blueprint $table) {
                $table->dropColumn('dernier_message_id');
            });
        }
    }
};
