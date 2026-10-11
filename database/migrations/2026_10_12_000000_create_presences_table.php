<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('presences')) {
            return;
        }

        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affectation_id')->constrained('affectations_cours')->cascadeOnDelete();
            $table->foreignId('eleve_id')->constrained()->cascadeOnDelete();
            $table->date('date_seance');
            $table->enum('statut_presence', ['present', 'absent', 'retard', 'excuse'])->default('present');
            $table->string('motif', 200)->nullable();
            $table->foreignId('enregistre_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['affectation_id', 'eleve_id', 'date_seance'], 'presences_seance_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
