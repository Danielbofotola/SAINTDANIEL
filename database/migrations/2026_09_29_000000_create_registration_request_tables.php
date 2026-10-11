<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_inscription', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 50);
            $table->string('postnom', 50)->nullable();
            $table->string('prenom', 50);
            $table->enum('sexe', ['M', 'F']);
            $table->date('date_naissance');
            $table->string('lieu_naissance', 100)->nullable();
            $table->foreignId('classe_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('annee_scolaire_id')->nullable()->constrained('annees_scolaires')->nullOnDelete();
            $table->string('nom_mere', 100);
            $table->string('telephone_mere', 20);
            $table->string('email_mere', 100)->nullable();
            $table->text('adresse_mere')->nullable();
            $table->string('nom_pere', 100)->nullable();
            $table->string('telephone_pere', 20)->nullable();
            $table->string('email_pere', 100)->nullable();
            $table->string('statut', 20)->default('en_attente');
            $table->foreignId('traite_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('traite_le')->nullable();
            $table->text('motif_refus')->nullable();
            $table->timestamps();
            $table->index(['statut', 'created_at']);
        });

        Schema::create('demandes_comptes', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 50);
            $table->string('postnom', 50)->nullable();
            $table->string('prenom', 50);
            $table->string('username', 50);
            $table->string('email', 100);
            $table->string('telephone', 20)->nullable();
            $table->string('role_souhaite', 20);
            $table->string('mot_de_passe')->nullable();
            $table->string('statut', 20)->default('en_attente');
            $table->foreignId('traite_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('traite_le')->nullable();
            $table->text('motif_refus')->nullable();
            $table->timestamps();
            $table->index(['statut', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_comptes');
        Schema::dropIfExists('demandes_inscription');
    }
};
