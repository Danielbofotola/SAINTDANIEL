<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $optionIndexes = collect(DB::select('SHOW INDEX FROM options'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        Schema::table('options', function (Blueprint $table) {
            $table->index('section_id', 'options_section_id_index');
        });

        if (in_array('options_section_id_code_unique', $optionIndexes, true)) {
            Schema::table('options', function (Blueprint $table) {
                $table->dropUnique('options_section_id_code_unique');
            });
        }

        Schema::table('options', function (Blueprint $table) use ($optionIndexes) {
            $table->dropUnique('options_section_id_nom_unique');
            $table->dropColumn(['code', 'description', 'statut']);
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique('sections_nom_unique');
            $table->dropColumn(['code', 'description', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->string('code', 30)->unique()->after('id');
            $table->text('description')->nullable()->after('nom');
            $table->enum('statut', ['actif', 'inactif'])->default('actif')->after('description');
            $table->unique('nom');
        });

        Schema::table('options', function (Blueprint $table) {
            $table->string('code', 30)->after('section_id');
            $table->text('description')->nullable()->after('nom');
            $table->enum('statut', ['actif', 'inactif'])->default('actif')->after('description');
            $table->unique(['section_id', 'code']);
            $table->unique(['section_id', 'nom']);
        });
    }
};