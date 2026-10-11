<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'ecritures', 'paies', 'salaires', 'depenses', 'categories_depenses', 'paiements',
        'frais_scolaires', 'types_frais', 'inscriptions', 'eleves', 'tuteurs', 'classes',
        'cycles', 'professeurs', 'annees_scolaires', 'users',
    ] as $table) {
        Schema::dropIfExists($table);
    }
    Schema::enableForeignKeyConstraints();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable()->unique();
        $table->string('nom', 50)->nullable();
        $table->string('postnom', 50)->nullable();
        $table->string('prenom', 50)->nullable();
        $table->string('username')->unique();
        $table->string('email')->unique();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->rememberToken();
        $table->timestamps();
    });
    Schema::create('annees_scolaires', function (Blueprint $table) {
        $table->id();
        $table->string('libelle');
        $table->date('date_debut');
        $table->date('date_fin');
        $table->boolean('is_encours')->default(false);
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('cycles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->string('nom');
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('matricule')->unique();
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('sexe');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('tuteurs', function (Blueprint $table) {
        $table->id();
        $table->string('nom_mere');
        $table->string('telephone_mere');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('eleves', function (Blueprint $table) {
        $table->id();
        $table->string('matricule')->unique();
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('sexe');
        $table->date('date_naissance');
        $table->unsignedBigInteger('tuteur_id');
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('inscriptions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->date('date_inscription');
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('types_frais', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 100);
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('frais_scolaires', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('type_frais_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->decimal('montant_exige', 10, 2);
        $table->date('date_echeance');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('paiements', function (Blueprint $table) {
        $table->id();
        $table->string('num_recu', 50)->unique();
        $table->unsignedBigInteger('inscription_id');
        $table->unsignedBigInteger('frais_scolaire_id');
        $table->unsignedBigInteger('periode_id')->nullable();
        $table->decimal('montant_paye', 10, 2);
        $table->dateTime('date_paiement')->nullable();
        $table->string('mode_paiement')->default('especes');
        $table->string('reference_transaction', 100)->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('categories_depenses', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 100);
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('depenses', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->unsignedBigInteger('category_id');
        $table->string('motif');
        $table->decimal('montant', 10, 2);
        $table->date('date_depense');
        $table->string('piece_justificative')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('salaires', function (Blueprint $table) {
        $table->id();
        $table->string('beneficiaire_type', 20);
        $table->unsignedBigInteger('beneficiaire_id');
        $table->decimal('salaire_base', 12, 2);
        $table->string('devise', 10)->default('USD');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('paies', function (Blueprint $table) {
        $table->id();
        $table->string('num_paie', 50)->unique();
        $table->unsignedBigInteger('salaire_id');
        $table->string('periode', 7);
        $table->decimal('montant_base', 12, 2);
        $table->decimal('primes', 12, 2)->default(0);
        $table->decimal('retenues', 12, 2)->default(0);
        $table->decimal('montant_net', 12, 2);
        $table->date('date_paiement');
        $table->string('mode_paiement', 20)->default('especes');
        $table->string('reference', 100)->nullable();
        $table->string('observation')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
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
    });

    DB::table('annees_scolaires')->insert(['id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif']);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'nom' => '3e A', 'statut' => 'actif']);
    $tuteurId = DB::table('tuteurs')->insertGetId(['nom_mere' => 'Marie', 'telephone_mere' => '0990', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $eleveId = DB::table('eleves')->insertGetId(['matricule' => 'ELV-2026-0001', 'nom' => 'Mwamba', 'postnom' => 'T', 'prenom' => 'Aline', 'sexe' => 'F', 'date_naissance' => '2014-05-12', 'tuteur_id' => $tuteurId, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('inscriptions')->insert(['id' => 1, 'eleve_id' => $eleveId, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-05', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $typeId = DB::table('types_frais')->insertGetId(['nom' => 'Frais scolaires', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('frais_scolaires')->insert(['id' => 1, 'type_frais_id' => $typeId, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'montant_exige' => 300, 'date_echeance' => '2026-10-31', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('paiements')->insert(['num_recu' => 'REC-1', 'inscription_id' => 1, 'frais_scolaire_id' => 1, 'montant_paye' => 120, 'date_paiement' => '2026-10-02', 'mode_paiement' => 'especes', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $categorieId = DB::table('categories_depenses')->insertGetId(['nom' => 'Fournitures', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('depenses')->insert(['annee_scolaire_id' => 1, 'category_id' => $categorieId, 'motif' => 'Craies', 'montant' => 40, 'date_depense' => '2026-10-03', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
});

test('la page Statistiques montre les répartitions', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.statistiques.index'))
        ->assertOk()
        ->assertSee('Répartition des élèves par sexe')
        ->assertSee('chartSexe', false)
        ->assertSee('chartClasses', false)
        ->assertSee('chartRecouvrement', false)
        ->assertSee('chartNature', false)
        // La courbe d'évolution vit sur sa propre page.
        ->assertDontSee('chartEvolution')
        ->assertSee(route('admin.statistiques.evolution'), false)
        ->assertSee(route('admin.dashboard'), false)
        ->assertSee('Recettes')
        ->assertSee('120,00')
        ->assertSee('40,00')
        ->assertSee('Fournitures');
});

test('la page Évolution montre les séries mensuelles', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.statistiques.evolution'))
        ->assertOk()
        ->assertSee('Évolution mensuelle')
        ->assertSee('chartEvolution', false)
        ->assertSee('chartInscriptions', false)
        ->assertSee('Solde')
        ->assertSee(route('admin.statistiques.index'), false)
        ->assertSee('120,00');
});

test('the statistics page is restricted to the admin role', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->get(route('admin.statistiques.index'))
        ->assertForbidden();

    $this->actingAs($comptable)
        ->get(route('admin.statistiques.evolution'))
        ->assertForbidden();
});
