<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('paiements', 'periode_id')) {
            Schema::table('paiements', function (Blueprint $table) {
                $table->foreignId('periode_id')->nullable()->after('frais_scolaire_id')->constrained('periodes')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('paiements', 'periode_id')) {
            Schema::table('paiements', function (Blueprint $table) {
                $table->dropConstrainedForeignId('periode_id');
            });
        }
    }
};
