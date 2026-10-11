<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Messages envoyés depuis la page publique « Contact » (/site/contact).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('email', 100);
            $table->string('telephone', 20)->nullable();
            $table->string('sujet', 120);
            $table->text('message');
            $table->string('statut', 20)->default('nouveau'); // nouveau | traite
            $table->dateTime('traite_le')->nullable();
            $table->unsignedBigInteger('traite_par')->nullable();
            $table->string('adresse_ip', 45)->nullable();
            $table->timestamps();

            $table->index('statut');
            $table->index('created_at');

            $table->foreign('traite_par')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
