<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $foreignKeyExists = collect(Schema::getForeignKeys('users'))
            ->contains(fn (array $foreignKey) => $foreignKey['name'] === 'users_professeur_id_foreign');

        if ($foreignKeyExists) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('professeur_id')
                ->references('id')
                ->on('professeurs')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        $foreignKeyExists = collect(Schema::getForeignKeys('users'))
            ->contains(fn (array $foreignKey) => $foreignKey['name'] === 'users_professeur_id_foreign');

        if (! $foreignKeyExists) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['professeur_id']);
        });
    }
};
