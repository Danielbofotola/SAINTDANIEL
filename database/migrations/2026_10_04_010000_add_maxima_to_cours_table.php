<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cours', 'max_travaux_journaliers')) {
            Schema::table('cours', function (Blueprint $table) {
                $table->unsignedInteger('max_travaux_journaliers')->nullable()->after('ponderation');
            });
        }

        if (! Schema::hasColumn('cours', 'max_examen')) {
            Schema::table('cours', function (Blueprint $table) {
                $table->unsignedInteger('max_examen')->nullable()->after('max_travaux_journaliers');
            });
        }
    }

    public function down(): void
    {
        foreach (['max_travaux_journaliers', 'max_examen'] as $column) {
            if (Schema::hasColumn('cours', $column)) {
                Schema::table('cours', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
