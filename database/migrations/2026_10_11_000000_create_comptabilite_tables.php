<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('comptes_comptables')) {
            Schema::create('comptes_comptables', function (Blueprint $table) {
                $table->id();
                $table->string('numero', 10)->unique();
                $table->string('libelle', 150);
                $table->unsignedTinyInteger('classe');
                $table->string('type', 20)->default('charge'); // actif | passif | charge | produit
                $table->string('statut')->default('actif');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ecritures')) {
            Schema::create('ecritures', function (Blueprint $table) {
                $table->id();
                $table->date('date_ecriture');
                $table->string('journal', 10)->default('OD');
                $table->string('libelle', 200);
                $table->string('reference', 100)->nullable();
                $table->string('source_type', 30)->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->decimal('total_debit', 14, 2)->default(0);
                $table->decimal('total_credit', 14, 2)->default(0);
                $table->string('statut')->default('validee');
                $table->timestamps();
                $table->unique(['source_type', 'source_id'], 'ecritures_source_unique');
            });
        }

        if (! Schema::hasTable('lignes_ecriture')) {
            Schema::create('lignes_ecriture', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ecriture_id')->constrained('ecritures')->cascadeOnDelete();
                $table->foreignId('compte_id')->constrained('comptes_comptables')->restrictOnDelete();
                $table->string('libelle', 200)->nullable();
                $table->decimal('debit', 14, 2)->default(0);
                $table->decimal('credit', 14, 2)->default(0);
                $table->timestamps();
            });
        }

        $this->seedPlanComptable();
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_ecriture');
        Schema::dropIfExists('ecritures');
        Schema::dropIfExists('comptes_comptables');
    }

    /**
     * Plan comptable SYSCOHADA (sous-ensemble usuel d'un établissement scolaire).
     */
    private function seedPlanComptable(): void
    {
        if (DB::table('comptes_comptables')->exists()) {
            return;
        }

        $comptes = [
            // Classe 1 — Ressources durables
            ['101', 'Capital social', 1, 'passif'],
            ['106', 'Réserves', 1, 'passif'],
            ['110', 'Report à nouveau créditeur', 1, 'passif'],
            ['120', 'Résultat de l’exercice', 1, 'passif'],
            ['160', 'Emprunts et dettes assimilées', 1, 'passif'],
            // Classe 2 — Actif immobilisé
            ['220', 'Terrains', 2, 'actif'],
            ['230', 'Bâtiments', 2, 'actif'],
            ['240', 'Matériel, mobilier et actifs biologiques', 2, 'actif'],
            ['280', 'Amortissements des immobilisations', 2, 'actif'],
            // Classe 3 — Stocks
            ['310', 'Marchandises', 3, 'actif'],
            ['330', 'Autres approvisionnements', 3, 'actif'],
            // Classe 4 — Tiers
            ['401', 'Fournisseurs, dettes en compte', 4, 'passif'],
            ['411', 'Élèves et parents (créances)', 4, 'actif'],
            ['421', 'Personnel, avances et acomptes', 4, 'actif'],
            ['422', 'Personnel, rémunérations dues', 4, 'passif'],
            ['431', 'Organismes sociaux', 4, 'passif'],
            ['441', 'État et collectivités publiques', 4, 'passif'],
            ['461', 'Associés et groupe', 4, 'passif'],
            // Classe 5 — Trésorerie
            ['521', 'Banques locales', 5, 'actif'],
            ['531', 'Chèques postaux', 5, 'actif'],
            ['571', 'Caisse siège social', 5, 'actif'],
            ['585', 'Virements de fonds internes', 5, 'actif'],
            // Classe 6 — Charges
            ['601', 'Achats de marchandises', 6, 'charge'],
            ['604', 'Achats stockés de matières et fournitures', 6, 'charge'],
            ['605', 'Autres achats (fournitures, eau, électricité)', 6, 'charge'],
            ['611', 'Transports', 6, 'charge'],
            ['622', 'Locations et charges locatives', 6, 'charge'],
            ['624', 'Entretien, réparations et maintenance', 6, 'charge'],
            ['625', 'Primes d’assurance', 6, 'charge'],
            ['627', 'Publicité, publications, relations publiques', 6, 'charge'],
            ['631', 'Frais bancaires', 6, 'charge'],
            ['632', 'Rémunérations d’intermédiaires et honoraires', 6, 'charge'],
            ['638', 'Autres charges externes', 6, 'charge'],
            ['641', 'Impôts et taxes directs', 6, 'charge'],
            ['646', 'Droits d’enregistrement', 6, 'charge'],
            ['658', 'Charges diverses', 6, 'charge'],
            ['661', 'Rémunérations du personnel', 6, 'charge'],
            ['663', 'Charges sociales', 6, 'charge'],
            ['668', 'Autres charges de personnel', 6, 'charge'],
            ['671', 'Intérêts des emprunts', 6, 'charge'],
            ['681', 'Dotations aux amortissements d’exploitation', 6, 'charge'],
            ['691', 'Dotations aux provisions d’exploitation', 6, 'charge'],
            // Classe 7 — Produits
            ['701', 'Ventes de marchandises', 7, 'produit'],
            ['706', 'Services vendus (frais scolaires, écolage)', 7, 'produit'],
            ['707', 'Produits accessoires', 7, 'produit'],
            ['758', 'Produits divers', 7, 'produit'],
            ['771', 'Revenus financiers', 7, 'produit'],
            ['781', 'Reprises de provisions d’exploitation', 7, 'produit'],
            // Classe 8 — Autres charges et produits
            ['831', 'Charges hors activités ordinaires', 8, 'charge'],
            ['841', 'Produits hors activités ordinaires', 8, 'produit'],
            ['851', 'Dotations hors activités ordinaires', 8, 'charge'],
            ['861', 'Reprises hors activités ordinaires', 8, 'produit'],
            ['891', 'Impôts sur le résultat', 8, 'charge'],
        ];

        $maintenant = now();

        DB::table('comptes_comptables')->insert(array_map(fn (array $compte) => [
            'numero' => $compte[0],
            'libelle' => $compte[1],
            'classe' => $compte[2],
            'type' => $compte[3],
            'statut' => 'actif',
            'created_at' => $maintenant,
            'updated_at' => $maintenant,
        ], $comptes));
    }
};
