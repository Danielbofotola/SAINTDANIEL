<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Certificats et diplômes réellement délivrés à un élève.
 *
 * À l'application du passage, un élève admis qui clôt un cycle dont la fin est
 * déclarée (« Certificat » ou « Diplôme ») obtient une ligne ici : c'est la
 * trace de ce qu'il a reçu, indépendante de la configuration du cycle.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('certifications')) {
            Schema::create('certifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('eleve_id')->constrained('eleves')->cascadeOnDelete();
                $table->foreignId('cycle_id')->nullable()->constrained('cycles')->nullOnDelete();
                $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->restrictOnDelete();
                $table->string('libelle', 100);
                $table->date('date_obtention');
                $table->timestamps();

                $table->unique(['eleve_id', 'cycle_id', 'annee_scolaire_id'], 'certifications_eleve_cycle_annee_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
