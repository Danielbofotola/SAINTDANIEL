<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Place la colonne professeur_id après 'id' (optionnel)
            $table->foreignId('professeur_id')
                  ->nullable()
                  ->after('id') 
                  ->unique()
                  ->constrained('professeurs')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprime d'abord la contrainte de clé étrangère puis la colonne
            $table->dropForeign(['professeur_id']);
            $table->dropColumn('professeur_id');
        });
    }
};