<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('types_frais', 'est_inscription')) {
            Schema::table('types_frais', function (Blueprint $table) {
                $table->boolean('est_inscription')->default(false)->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('types_frais', 'est_inscription')) {
            Schema::table('types_frais', function (Blueprint $table) {
                $table->dropColumn('est_inscription');
            });
        }
    }
};
