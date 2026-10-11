<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ajoute le rôle « parent » à la colonne users.role.
 *
 * SQLite stocke un enum sous forme de simple varchar : il n'y a rien à
 * élargir. Seul MySQL impose de redéfinir l'énumération.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE users MODIFY role "
            ."ENUM('admin','prefetture','titulaire','professeur','comptable','parent') "
            ."NOT NULL DEFAULT 'professeur'"
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('users')->where('role', 'parent')->update(['role' => 'professeur']);

        DB::statement(
            "ALTER TABLE users MODIFY role "
            ."ENUM('admin','prefetture','titulaire','professeur','comptable') "
            ."NOT NULL DEFAULT 'professeur'"
        );
    }
};
