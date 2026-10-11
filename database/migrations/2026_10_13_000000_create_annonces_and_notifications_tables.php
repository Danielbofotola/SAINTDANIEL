<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Journal des annonces lancées par l'administration.
        if (! Schema::hasTable('annonces')) {
            Schema::create('annonces', function (Blueprint $table) {
                $table->id();
                $table->string('titre', 150);
                $table->text('message');
                $table->string('niveau', 20)->default('info'); // info | success | warning | danger
                $table->string('lien', 255)->nullable();
                $table->string('cible', 30)->default('tous');   // tous | admin | prefetture | titulaire | professeur | comptable
                $table->foreignId('envoye_par')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedInteger('destinataires')->default(0);
                $table->timestamps();
            });
        }

        // Notifications Laravel (canal « database »), une ligne par destinataire.
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('annonces');
    }
};
