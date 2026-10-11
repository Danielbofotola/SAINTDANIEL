<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * L'ancien réglage par défaut du titre du bulletin (« Cycle terminal de
 * l'éducation de base (CTEB) ») a été enregistré en base sur les installations
 * existantes et s'imposait à tous les bulletins. On le remet à vide pour que le
 * titre soit désormais déduit du cycle / de la section / de l'option réels de la
 * classe. Un titre explicitement personnalisé par l'établissement est conservé.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('parametres_systeme') || ! Schema::hasColumn('parametres_systeme', 'bulletin_titre')) {
            return;
        }

        DB::table('parametres_systeme')
            ->where('bulletin_titre', 'Bulletin annuel — Cycle terminal de l’éducation de base (CTEB)')
            ->update(['bulletin_titre' => null]);
    }

    public function down(): void
    {
        // Rien à restaurer : le titre se déduit désormais de la structure réelle.
    }
};
