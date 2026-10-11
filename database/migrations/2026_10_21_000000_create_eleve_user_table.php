<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rattachement « compte parent ↔ enfant(s) » : un compte parent peut suivre
 * plusieurs élèves, et un élève peut avoir plusieurs comptes parents (père et
 * mère, tuteur légal…). C'est l'administrateur qui fait le lien.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('eleve_user')) {
            Schema::create('eleve_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('eleve_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->unique(['eleve_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('eleve_user');
    }
};
