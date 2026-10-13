<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registre disciplinaire : sanctions prononcées par le Préfet des Études
 * (consigne, avertissement, exclusion temporaire ou définitive).
 *
 * Chaque sanction est rattachée à un élève, à son année scolaire et, si connue,
 * à sa classe (photographiée au moment des faits). Elle peut être levée.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sanctions')) {
            Schema::create('sanctions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('eleve_id')->constrained('eleves')->cascadeOnDelete();
                $table->foreignId('classe_id')->nullable()->constrained('classes')->nullOnDelete();
                $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->restrictOnDelete();
                // consigne | avertissement | exclusion_temporaire | exclusion_definitive
                $table->string('type', 30);
                $table->text('motif');
                $table->date('date_sanction');
                $table->date('date_fin')->nullable();
                $table->text('observations')->nullable();
                // en_cours | levee
                $table->string('statut', 20)->default('en_cours');
                $table->foreignId('prononce_par')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('levee_le')->nullable();
                $table->timestamps();

                $table->index(['annee_scolaire_id', 'statut']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sanctions');
    }
};
