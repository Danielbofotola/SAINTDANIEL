<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eleves', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 50);
            $table->string('postnom', 50)->nullable();
            $table->string('prenom', 50);
            $table->enum('sexe', ['M', 'F']);
            $table->string('classe', 100);
            $table->string('cycle', 100);
            $table->string('photo')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eleves');
    }
};
