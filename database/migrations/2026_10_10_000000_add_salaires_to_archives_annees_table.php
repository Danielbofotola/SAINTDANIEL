<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les salaires versés (table `paies`) n'entraient pas dans la clôture :
     * on les fige aussi, et ils sont désormais déduits du solde final.
     */
    public function up(): void
    {
        Schema::table('archives_annees', function (Blueprint $table) {
            if (! Schema::hasColumn('archives_annees', 'total_salaires')) {
                $table->decimal('total_salaires', 12, 2)->default(0)->after('total_impayes');
            }
            if (! Schema::hasColumn('archives_annees', 'nombre_paies')) {
                $table->unsignedInteger('nombre_paies')->default(0)->after('total_salaires');
            }
        });
    }

    public function down(): void
    {
        Schema::table('archives_annees', function (Blueprint $table) {
            $table->dropColumn(['total_salaires', 'nombre_paies']);
        });
    }
};
