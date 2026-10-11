<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professeurs', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 30)->unique();
            $table->string('nom', 50);
            $table->string('postnom', 50);
            $table->string('prenom', 50);
            $table->enum('sexe', ['M', 'F']);
            $table->string('specialite', 100);
            $table->string('telephone', 20);
            $table->string('email', 100)->unique();
            $table->string('photo', 255)->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professeurs');
    }
};