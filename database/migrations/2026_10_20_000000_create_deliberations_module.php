<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module de délibération : un ordre de progression sur les classes (qui
 * n'existait pas), les critères de décision dans les paramètres, et la
 * décision persistée par élève et par année.
 *
 * La moyenne utilisée est celle du bulletin (`BulletinRdc`) : aucune formule
 * n'est dupliquée ici, la délibération ne fait que décider.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('classes') && ! Schema::hasColumn('classes', 'ordre')) {
            Schema::table('classes', function (Blueprint $table) {
                // Rang de progression dans le cycle (1, 2, 3…). La classe
                // suivante est celle qui a l'ordre immédiatement supérieur.
                $table->unsignedInteger('ordre')->nullable()->after('nom');
            });
        }

        if (Schema::hasTable('parametres_systeme')) {
            Schema::table('parametres_systeme', function (Blueprint $table) {
                if (! Schema::hasColumn('parametres_systeme', 'deliberation_active')) {
                    $table->boolean('deliberation_active')->default(false);
                }
                if (! Schema::hasColumn('parametres_systeme', 'deliberation_seuil_admission')) {
                    $table->decimal('deliberation_seuil_admission', 5, 2)->default(50);
                }
                if (! Schema::hasColumn('parametres_systeme', 'deliberation_seuil_repechage')) {
                    $table->decimal('deliberation_seuil_repechage', 5, 2)->default(45);
                }
                if (! Schema::hasColumn('parametres_systeme', 'deliberation_max_cours_echoues')) {
                    $table->unsignedInteger('deliberation_max_cours_echoues')->nullable();
                }
                if (! Schema::hasColumn('parametres_systeme', 'deliberation_note_min_cours')) {
                    $table->decimal('deliberation_note_min_cours', 5, 2)->nullable();
                }
            });
        }

        if (! Schema::hasTable('deliberations')) {
            Schema::create('deliberations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inscription_id')->constrained('inscriptions')->cascadeOnDelete();
                $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->cascadeOnDelete();
                $table->decimal('pourcentage_general', 5, 2)->default(0);
                $table->unsignedInteger('cours_echoues')->default(0);
                $table->string('decision', 20)->default('redouble');   // admis | ajourne | redouble
                $table->text('critere')->nullable();                    // photo des critères appliqués
                $table->boolean('manuelle')->default(false);            // décision forcée à la main
                $table->unsignedBigInteger('classe_suivante_id')->nullable();
                $table->unsignedBigInteger('decide_par')->nullable();
                $table->timestamp('decide_le')->nullable();
                $table->text('commentaire')->nullable();
                $table->timestamps();
                $table->unique(['inscription_id', 'annee_scolaire_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deliberations');

        if (Schema::hasTable('parametres_systeme')) {
            Schema::table('parametres_systeme', function (Blueprint $table) {
                foreach ([
                    'deliberation_active', 'deliberation_seuil_admission', 'deliberation_seuil_repechage',
                    'deliberation_max_cours_echoues', 'deliberation_note_min_cours',
                ] as $colonne) {
                    if (Schema::hasColumn('parametres_systeme', $colonne)) {
                        $table->dropColumn($colonne);
                    }
                }
            });
        }

        if (Schema::hasTable('classes') && Schema::hasColumn('classes', 'ordre')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropColumn('ordre');
            });
        }
    }
};
