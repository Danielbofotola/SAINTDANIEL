<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Journal des décisions exécutives : le Préfet y publie décisions et injonctions
 * (horodatées, avec l'auteur), le Système le consulte comme journal d'audit.
 * Ajoute aussi le poste « Disciplinaire / Conseiller Pédagogique ».
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('journal_decisions')) {
            Schema::create('journal_decisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('annee_scolaire_id')->nullable()->constrained('annees_scolaires')->nullOnDelete();
                $table->foreignId('auteur_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('type', ['decision', 'injonction'])->default('decision');
                $table->string('objet', 160);
                $table->text('corps');
                $table->string('destinataires', 255)->nullable();
                $table->date('echeance')->nullable();
                $table->dateTime('publie_le')->nullable();
                $table->timestamps();
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE users MODIFY role ENUM('admin','systeme','prefetture','directeur_etudes','secretaire','disciplinaire','titulaire','professeur','comptable','parent') NOT NULL DEFAULT 'professeur'"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_decisions');

        if (DB::getDriverName() === 'mysql') {
            DB::table('users')->where('role', 'disciplinaire')->update(['role' => 'titulaire']);
        }
    }
};
