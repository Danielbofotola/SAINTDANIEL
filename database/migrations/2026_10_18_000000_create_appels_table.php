<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Appels audio/vidéo entre deux participants d'une conversation.
 *
 * La signalisation WebRTC (offre SDP, réponse SDP, candidats ICE) transite par
 * cette table : le client interroge son état en boucle courte. Aucun serveur
 * temps réel n'est requis (pas de WebSocket).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('appels')) {
            return;
        }

        Schema::create('appels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('appelant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('destinataire_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 10)->default('audio');       // audio | video
            $table->string('statut', 20)->default('sonnerie');  // sonnerie | accepte | refuse | termine
            $table->text('offre')->nullable();
            $table->text('reponse')->nullable();
            $table->text('ice_appelant')->nullable();
            $table->text('ice_destinataire')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appels');
    }
};
