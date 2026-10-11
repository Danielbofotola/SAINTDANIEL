<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réglages du bulletin (en-tête officiel, pied de page, seuil de passage et
 * éléments affichés), modifiables une fois pour toutes depuis l'écran
 * « Paramètres » de l'administration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parametres_systeme', function (Blueprint $table) {
            // En-tête officiel du bulletin.
            $table->string('bulletin_pays', 100)->nullable()->default('République Démocratique du Congo');
            $table->string('bulletin_ministere', 160)->nullable()->default("Ministère de l'Enseignement Primaire, Secondaire et Professionnel");
            $table->string('bulletin_titre', 160)->nullable()->default('Bulletin annuel — Cycle terminal de l’éducation de base (CTEB)');
            $table->string('bulletin_reference', 50)->nullable()->default('EBE/P.S./010');
            $table->string('bulletin_province', 100)->nullable();
            $table->string('bulletin_ville', 100)->nullable();
            $table->string('bulletin_commune', 100)->nullable();
            $table->string('bulletin_code_ecole', 50)->nullable();

            // Pied de page : signature.
            $table->string('bulletin_lieu_signature', 100)->nullable();
            $table->string('bulletin_signataire', 120)->nullable()->default('Le Chef d’Établissement');

            // Règles et éléments affichés.
            $table->decimal('bulletin_seuil_reussite', 5, 2)->default(50);
            $table->boolean('bulletin_afficher_place')->default(true);
            $table->boolean('bulletin_afficher_conduite')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('parametres_systeme', function (Blueprint $table) {
            $table->dropColumn([
                'bulletin_pays', 'bulletin_ministere', 'bulletin_titre', 'bulletin_reference',
                'bulletin_province', 'bulletin_ville', 'bulletin_commune', 'bulletin_code_ecole',
                'bulletin_lieu_signature', 'bulletin_signataire', 'bulletin_seuil_reussite',
                'bulletin_afficher_place', 'bulletin_afficher_conduite',
            ]);
        });
    }
};
