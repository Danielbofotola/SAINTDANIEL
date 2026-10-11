<?php

use App\Models\User;
use App\Support\Comptabilite;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'lignes_ecriture', 'ecritures', 'comptes_comptables', 'paies', 'salaires', 'depenses',
        'categories_depenses', 'paiements', 'frais_scolaires', 'types_frais', 'inscriptions',
        'eleves', 'tuteurs', 'classes', 'salles', 'cycles', 'professeurs', 'annees_scolaires', 'users',
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
    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('matricule')->unique();
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('sexe');
        $table->string('specialite')->nullable();
        $table->string('telephone')->nullable();
        $table->string('email')->nullable();
        $table->string('statut')->default('actif');
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
    Schema::create('comptes_comptables', function (Blueprint $table) {
        $table->id();
        $table->string('numero', 10)->unique();
        $table->string('libelle', 150);
        $table->unsignedTinyInteger('classe');
        $table->string('type', 20)->default('charge');
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
        $table->unique(['source_type', 'source_id']);
    });
    Schema::create('lignes_ecriture', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('ecriture_id');
        $table->unsignedBigInteger('compte_id');
        $table->string('libelle', 200)->nullable();
        $table->decimal('debit', 14, 2)->default(0);
        $table->decimal('credit', 14, 2)->default(0);
        $table->timestamps();
    });

    // Sous-ensemble du plan SYSCOHADA utile aux tests.
    foreach ([
        ['521', 'Banques locales', 5, 'actif'],
        ['571', 'Caisse siège social', 5, 'actif'],
        ['605', 'Autres achats (fournitures)', 6, 'charge'],
        ['658', 'Charges diverses', 6, 'charge'],
        ['661', 'Rémunérations du personnel', 6, 'charge'],
        ['706', 'Services vendus (frais scolaires)', 7, 'produit'],
    ] as $compte) {
        DB::table('comptes_comptables')->insert([
            'numero' => $compte[0], 'libelle' => $compte[1], 'classe' => $compte[2], 'type' => $compte[3],
            'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    DB::table('annees_scolaires')->insert(['id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif']);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'nom' => '3e A', 'statut' => 'actif']);
    $tuteurId = DB::table('tuteurs')->insertGetId(['nom_mere' => 'Marie', 'telephone_mere' => '0990', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $eleveId = DB::table('eleves')->insertGetId(['matricule' => 'ELV-2026-0001', 'nom' => 'Mwamba', 'postnom' => 'T', 'prenom' => 'Aline', 'sexe' => 'F', 'date_naissance' => '2014-05-12', 'tuteur_id' => $tuteurId, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('inscriptions')->insert(['id' => 1, 'eleve_id' => $eleveId, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-05', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $typeFraisId = DB::table('types_frais')->insertGetId(['nom' => 'Frais scolaires', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('frais_scolaires')->insert(['id' => 1, 'type_frais_id' => $typeFraisId, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'montant_exige' => 150, 'date_echeance' => '2026-10-31', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('professeurs')->insert(['id' => 1, 'matricule' => 'PROF-2026-0001', 'nom' => 'Kabila', 'postnom' => 'M', 'prenom' => 'Aline', 'sexe' => 'F', 'specialite' => 'Maths', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
});

function idCompte(string $numero): int
{
    return (int) DB::table('comptes_comptables')->where('numero', $numero)->value('id');
}

test('a balanced manual entry is stored and an unbalanced one is refused', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.comptabilite.index'))
        ->assertOk()
        ->assertSee('Comptabilité OHADA');

    $this->actingAs($admin)
        ->post(route('admin.comptabilite.ecritures.store'), [
            'date_ecriture' => '2026-10-05', 'journal' => 'OD', 'libelle' => 'Apport en caisse',
            'lignes' => [
                ['compte_id' => idCompte('571'), 'debit' => 100],
                ['compte_id' => idCompte('706'), 'credit' => 100],
            ],
        ])
        ->assertRedirect();

    expect(DB::table('ecritures')->count())->toBe(1)
        ->and((float) DB::table('ecritures')->value('total_debit'))->toBe(100.0)
        ->and(DB::table('lignes_ecriture')->count())->toBe(2);

    // Déséquilibrée : refusée.
    $this->actingAs($admin)
        ->post(route('admin.comptabilite.ecritures.store'), [
            'date_ecriture' => '2026-10-05', 'journal' => 'OD', 'libelle' => 'Erreur',
            'lignes' => [
                ['compte_id' => idCompte('571'), 'debit' => 100],
                ['compte_id' => idCompte('706'), 'credit' => 60],
            ],
        ])
        ->assertSessionHas('error');

    expect(DB::table('ecritures')->count())->toBe(1);
});

test('payments, expenses and salaries are posted once and never duplicated', function () {
    DB::table('paiements')->insert(['num_recu' => 'REC-2026-2027-0001', 'inscription_id' => 1, 'frais_scolaire_id' => 1, 'montant_paye' => 100, 'date_paiement' => '2026-10-02', 'mode_paiement' => 'especes', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $categorieId = DB::table('categories_depenses')->insertGetId(['nom' => 'Fournitures', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('depenses')->insert(['annee_scolaire_id' => 1, 'category_id' => $categorieId, 'motif' => 'Craies', 'montant' => 40, 'date_depense' => '2026-10-03', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('salaires')->insert(['id' => 1, 'beneficiaire_type' => 'professeur', 'beneficiaire_id' => 1, 'salaire_base' => 70, 'devise' => 'USD', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('paies')->insert(['num_paie' => 'PAIE-2026-10-0001', 'salaire_id' => 1, 'periode' => '2026-10', 'montant_base' => 70, 'primes' => 0, 'retenues' => 0, 'montant_net' => 70, 'date_paiement' => '2026-10-04', 'mode_paiement' => 'especes', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.comptabilite.index'))
        ->assertOk()
        ->assertSee('1 paiement(s)');

    $this->actingAs($admin)
        ->post(route('admin.comptabilite.synchroniser'))
        ->assertSessionHas('success', fn (string $message) => str_contains($message, '1 paiement(s)'));

    expect(DB::table('ecritures')->count())->toBe(3);
    expect(DB::table('lignes_ecriture')->count())->toBe(6);

    // Comptes utilisés par la synchronisation.
    $numeroDuDebit = fn (string $libelle) => (string) DB::table('lignes_ecriture')
        ->join('comptes_comptables', 'comptes_comptables.id', '=', 'lignes_ecriture.compte_id')
        ->where('lignes_ecriture.ecriture_id', DB::table('ecritures')->where('source_type', $libelle)->value('id'))
        ->where('lignes_ecriture.debit', '>', 0)
        ->value('comptes_comptables.numero');

    expect($numeroDuDebit('paiement'))->toBe('571')   // encaissement en caisse
        ->and($numeroDuDebit('depense'))->toBe('605')  // achat de fournitures
        ->and($numeroDuDebit('paie'))->toBe('661');    // rémunération du personnel

    // Deuxième synchronisation : rien de nouveau.
    $this->actingAs($admin)
        ->post(route('admin.comptabilite.synchroniser'))
        ->assertSessionHas('success', fn (string $message) => str_contains($message, 'déjà comptabilisé'));

    expect(DB::table('ecritures')->count())->toBe(3);
});

test('the general balance totals debits and credits per account', function () {
    DB::table('paiements')->insert(['num_recu' => 'REC-2026-2027-0001', 'inscription_id' => 1, 'frais_scolaire_id' => 1, 'montant_paye' => 200, 'date_paiement' => '2026-10-02', 'mode_paiement' => 'banque', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    Comptabilite::synchroniser();

    $balance = Comptabilite::balance()->keyBy('numero');

    expect($balance['521']->debit)->toBe(200.0)
        ->and($balance['521']->credit)->toBe(0.0)
        ->and($balance['521']->solde)->toBe(200.0)
        ->and($balance['706']->credit)->toBe(200.0)
        ->and($balance['706']->solde)->toBe(-200.0)
        ->and(round((float) $balance->sum('debit'), 2))->toBe(round((float) $balance->sum('credit'), 2));

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)
        ->get(route('admin.comptabilite.balance'))
        ->assertOk()
        ->assertSee('Équilibrée')
        ->assertSee('Banques locales');
});

test('a custom account can be added to the chart and a used one cannot be removed', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.comptabilite.plan.store'), ['numero' => '616', 'libelle' => 'Transports du personnel', 'classe' => 6, 'type' => 'charge'])
        ->assertRedirect();

    expect(DB::table('comptes_comptables')->where('numero', '616')->count())->toBe(1);

    // Compte mouvementé : suppression refusée.
    DB::table('paiements')->insert(['num_recu' => 'REC-2026-2027-0002', 'inscription_id' => 1, 'frais_scolaire_id' => 1, 'montant_paye' => 10, 'date_paiement' => '2026-10-02', 'mode_paiement' => 'especes', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    Comptabilite::synchroniser();

    $this->actingAs($admin)
        ->delete(route('admin.comptabilite.plan.destroy', idCompte('571')))
        ->assertSessionHas('error');

    expect(DB::table('comptes_comptables')->where('numero', '571')->count())->toBe(1);

    // Compte libre : suppression acceptée.
    $this->actingAs($admin)
        ->delete(route('admin.comptabilite.plan.destroy', idCompte('616')))
        ->assertRedirect();

    expect(DB::table('comptes_comptables')->where('numero', '616')->count())->toBe(0);
});

test('every OHADA page offers a way back to the dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    foreach ([
        route('admin.comptabilite.index'),
        route('admin.comptabilite.plan'),
        route('admin.comptabilite.balance'),
    ] as $url) {
        $this->actingAs($admin)
            ->get($url)
            ->assertOk()
            ->assertSee('Tableau de bord')
            ->assertSee(route('admin.dashboard'), false);
    }
});
