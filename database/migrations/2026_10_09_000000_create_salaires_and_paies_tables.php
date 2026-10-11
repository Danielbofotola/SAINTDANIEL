<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('salaires')) {
            Schema::create('salaires', function (Blueprint $table) {
                $table->id();
                // Bénéficiaire : un professeur (entité) ou un autre membre du
                // personnel (compte utilisateur : comptable, titulaire, préfet…).
                $table->string('beneficiaire_type', 20);
                $table->unsignedBigInteger('beneficiaire_id');
                $table->decimal('salaire_base', 12, 2);
                $table->string('devise', 10)->default('USD');
                $table->string('statut')->default('actif');
                $table->timestamps();
                $table->unique(['beneficiaire_type', 'beneficiaire_id'], 'salaires_beneficiaire_unique');
            });
        }

        if (! Schema::hasTable('paies')) {
            Schema::create('paies', function (Blueprint $table) {
                $table->id();
                $table->string('num_paie', 50)->unique();
                $table->foreignId('salaire_id')->constrained('salaires')->cascadeOnDelete();
                $table->string('periode', 7); // « AAAA-MM »
                $table->decimal('montant_base', 12, 2);
                $table->decimal('primes', 12, 2)->default(0);
                $table->decimal('retenues', 12, 2)->default(0);
                $table->decimal('montant_net', 12, 2);
                $table->date('date_paiement');
                $table->string('mode_paiement', 20)->default('especes');
                $table->string('reference', 100)->nullable();
                $table->string('observation')->nullable();
                $table->string('statut')->default('actif');
                $table->timestamps();
                // Un seul paiement par personne et par mois.
                $table->unique(['salaire_id', 'periode'], 'paies_salaire_periode_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('paies');
        Schema::dropIfExists('salaires');
    }
};
