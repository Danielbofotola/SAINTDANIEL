<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Nouveaux postes de la matrice des permissions : « Système », « Secrétaire » et
 * « Directeur des Études ». L'énumération MySQL de la colonne `role` est
 * étendue (les autres moteurs utilisent une colonne texte, rien à faire).
 */
return new class extends Migration
{
    private const ROLES = "'admin','systeme','prefetture','directeur_etudes','secretaire','titulaire','professeur','comptable','parent'";

    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY role ENUM('.self::ROLES.") NOT NULL DEFAULT 'professeur'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (['systeme', 'directeur_etudes', 'secretaire'] as $role) {
            DB::table('users')->where('role', $role)->update(['role' => 'admin']);
        }

        DB::statement(
            "ALTER TABLE users MODIFY role "
            ."ENUM('admin','prefetture','titulaire','professeur','comptable','parent') "
            ."NOT NULL DEFAULT 'professeur'"
        );
    }
};
