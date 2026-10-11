<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'paiements', 'frais_scolaires', 'types_frais', 'inscriptions', 'periodes', 'eleves', 'tuteurs',
        'classes', 'cycles', 'salles', 'annees_scolaires', 'users', 'parametres_systeme',
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
        $table->date('date_naissance')->nullable();
        $table->string('username')->unique();
        $table->string('email')->unique();
        $table->string('telephone', 20)->nullable();
        $table->text('adresse')->nullable();
        $table->string('photo')->nullable();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->dateTime('last_login')->nullable();
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
    Schema::create('salles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('salle_id')->nullable();
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
        $table->text('description')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('frais_scolaires', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('type_frais_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->decimal('montant_exige', 10, 2);
        $table->string('devise', 3)->default('USD');
        $table->date('date_echeance');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('periodes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('nom', 50);
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
        $table->string('devise', 3)->default('USD');
        $table->decimal('taux', 12, 2)->nullable();
        $table->decimal('montant_recu', 12, 2)->nullable();
        $table->dateTime('date_paiement')->nullable();
        $table->string('mode_paiement')->default('especes');
        $table->string('reference_transaction', 100)->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('parametres_systeme', function (Blueprint $table) {
        $table->id();
        $table->string('nom_court', 60)->nullable();
        $table->boolean('module_recus_actif')->default(true);
        $table->boolean('module_recouvrement_actif')->default(true);
        $table->timestamps();
    });

    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif',
    ]);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);
    DB::table('periodes')->insert(['id' => 1, 'annee_scolaire_id' => 1, 'nom' => '1ère Période', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('parametres_systeme')->insert(['nom_court' => 'Ecole Test', 'module_recus_actif' => 1, 'module_recouvrement_actif' => 1, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'nom' => '3e A', 'statut' => 'actif']);
    $tuteurId = DB::table('tuteurs')->insertGetId(['nom_mere' => 'Marie', 'telephone_mere' => '0990', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $eleveId = DB::table('eleves')->insertGetId([
        'matricule' => 'ELV-2026-0001', 'nom' => 'Mwamba', 'postnom' => 'T', 'prenom' => 'Aline', 'sexe' => 'F',
        'date_naissance' => '2014-05-12', 'tuteur_id' => $tuteurId, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('inscriptions')->insert([
        'id' => 1, 'eleve_id' => $eleveId, 'classe_id' => 1, 'annee_scolaire_id' => 1,
        'date_inscription' => '2026-09-05', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $typeId = DB::table('types_frais')->insertGetId(['nom' => 'Frais scolaires', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('frais_scolaires')->insert([
        'id' => 1, 'type_frais_id' => $typeId, 'classe_id' => 1, 'annee_scolaire_id' => 1,
        'montant_exige' => 150, 'date_echeance' => '2026-10-31', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
});

test('the cashier sees the payment page with a student filter and can record a payment', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->get(route('comptable.paiements.index'))
        ->assertOk()
        ->assertSee('Encaisser un paiement')
        ->assertSee('eleveSearch')
        ->assertSee('Mwamba')
        ->assertSee('classesParFrais', false)
        ->assertSee('classesParInscription', false)
        // La date se saisit manuellement au format jj/mm/aaaa (masque JS).
        ->assertSee('data-date-fr', false)
        ->assertSee('js/date-fr.js', false);

    $this->actingAs($comptable)
        ->post(route('comptable.paiements.store'), [
            'inscription_id' => 1, 'frais_scolaire_id' => 1,
            'montant_paye' => 75, 'mode_paiement' => 'especes', 'classe_id' => 1,
        ])
        ->assertRedirect(route('comptable.paiements.index', ['classe_id' => 1]));

    $paiement = DB::table('paiements')->first();
    expect($paiement)->not->toBeNull()
        ->and($paiement->num_recu)->toBe('REC-2026-2027-0001')
        ->and((float) $paiement->montant_paye)->toBe(75.0);
});

test('the cashier payment page filters the student list by class', function () {
    DB::table('classes')->insert(['id' => 2, 'cycle_id' => 1, 'nom' => '4e B', 'statut' => 'actif']);
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->get(route('comptable.paiements.index', ['classe_id' => 2]))
        ->assertOk()
        ->assertDontSee('Mwamba');
});

test('the cashier dashboard links to the payment page', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->get(route('comptable.dashboard'))
        ->assertOk()
        ->assertSee(route('comptable.paiements.index'), false)
        ->assertSee(route('comptable.recouvrement.index'), false);
});

test('the payment page is restricted to the cashier role', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('comptable.paiements.index'))
        ->assertForbidden();
});

test('the receipt number is generated automatically and the payment keeps its period', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->post(route('comptable.paiements.store'), [
            'inscription_id' => 1, 'frais_scolaire_id' => 1, 'periode_id' => 1,
            'montant_paye' => 50, 'mode_paiement' => 'banque',
        ])
        ->assertRedirect();

    $paiement = DB::table('paiements')->first();
    expect($paiement->num_recu)->toBe('REC-2026-2027-0001')
        ->and((int) $paiement->periode_id)->toBe(1);
});

test('the receipt page shows the auto-generated number, the student and the period', function () {
    DB::table('paiements')->insert([
        'num_recu' => 'REC-2026-2027-0001', 'inscription_id' => 1, 'frais_scolaire_id' => 1, 'periode_id' => 1,
        'montant_paye' => 60, 'devise' => 'CDF', 'taux' => 2800, 'montant_recu' => 168000,
        'date_paiement' => now(), 'mode_paiement' => 'especes', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $paiementId = DB::table('paiements')->value('id');
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->get(route('comptable.paiements.recu', $paiementId))
        ->assertOk()
        ->assertSee('REC-2026-2027-0001')
        ->assertSee('Généré automatiquement')
        ->assertSee('Mwamba')
        ->assertSee('1ère Période')
        // Encaissé en francs : le reçu montre la devise reçue et le taux appliqué.
        ->assertSee('168 000 FC')
        ->assertSee('taux 2 800,00')
        ->assertSee('60,00 $');   // montant crédité dans la devise du tarif (USD)
});

test('the recovery page shows the collection and filters by period', function () {
    DB::table('paiements')->insert([
        'num_recu' => 'REC-2026-2027-0001', 'inscription_id' => 1, 'frais_scolaire_id' => 1, 'periode_id' => 1,
        'montant_paye' => 150, 'date_paiement' => now(), 'mode_paiement' => 'especes', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->get(route('comptable.recouvrement.index'))
        ->assertOk()
        ->assertSee('Recouvrement')
        ->assertSee('Taux de recouvrement')
        ->assertSee('100 %')
        ->assertSee('3e A')
        ->assertSee('id="tableRecouvrement"', false)
        ->assertSee('id="tablePaiements"', false)
        ->assertSee('dataTables.bootstrap5.min.js', false);

    $this->actingAs($comptable)
        ->get(route('comptable.recouvrement.index', ['periode_id' => 1]))
        ->assertOk()
        ->assertSee('1ère Période');
});

test('the cashier payment page only lists the tariffs of the selected class', function () {
    DB::table('classes')->insert(['id' => 2, 'cycle_id' => 1, 'nom' => '4e B', 'statut' => 'actif']);
    $typeId = DB::table('types_frais')->insertGetId(['nom' => 'Frais scolaires', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('frais_scolaires')->insert([
        'id' => 2, 'type_frais_id' => $typeId, 'classe_id' => 2, 'annee_scolaire_id' => 1,
        'montant_exige' => 90, 'date_echeance' => '2026-10-31', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->get(route('comptable.paiements.index', ['classe_id' => 1]))
        ->assertOk()
        ->assertSee('tarifsDisponibles', false)
        ->assertSee('Frais scolaires — 3e A', false)
        ->assertDontSee('— 4e B', false);
});

test('un paiement en francs congolais est converti dans la devise du tarif', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->post(route('comptable.paiements.store'), [
            'inscription_id' => 1, 'frais_scolaire_id' => 1,
            'montant_paye' => 140000, 'devise' => 'CDF', 'taux' => 2800,
            'mode_paiement' => 'especes',
        ])->assertRedirect();

    $paiement = DB::table('paiements')->first();

    expect($paiement->devise)->toBe('CDF')
        ->and((float) $paiement->montant_recu)->toBe(140000.0)
        ->and((float) $paiement->taux)->toBe(2800.0)
        // 140 000 FC ÷ 2800 = 50 $ — la devise du tarif alimente les totaux.
        ->and((float) $paiement->montant_paye)->toBe(50.0);
});

test('l’affichage des montants retire les décimales du franc congolais', function () {
    expect(\App\Support\Monnaie::format(50, 'USD'))->toBe('50,00 $')
        ->and(\App\Support\Monnaie::format(140000, 'CDF'))->toBe('140 000 FC')
        ->and(\App\Support\Monnaie::convertir(140000, 'CDF', 'USD', 2800))->toBe(50.0)
        ->and(\App\Support\Monnaie::convertir(50, 'USD', 'CDF', 2800))->toBe(140000.0);
});

test('la période et la référence sont automatiques au paiement', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    // Ni période ni référence fournies : le système les détermine.
    $this->actingAs($comptable)
        ->post(route('comptable.paiements.store'), [
            'inscription_id' => 1, 'frais_scolaire_id' => 1,
            'montant_paye' => 30, 'mode_paiement' => 'especes',
        ])->assertRedirect();

    $paiement = DB::table('paiements')->first();

    expect((int) $paiement->periode_id)->toBe(1)                              // période en cours
        ->and($paiement->reference_transaction)->toBe('REC-2026-2027-0001'); // référence = n° de reçu
});

test('le profil du caissier est propre au caissier', function () {
    $comptable = User::factory()->create(['role' => 'comptable', 'nom' => 'Kasa', 'prenom' => 'Yao']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($comptable)
        ->get(route('comptable.profil'))
        ->assertOk()
        ->assertSee('Mes informations')
        ->assertSee('Kasa');

    // Réservé au comptable.
    $this->actingAs($admin)->get(route('comptable.profil'))->assertForbidden();
});

test('les pages reçu et recouvrement sont optionnelles', function () {
    DB::table('paiements')->insert([
        'num_recu' => 'REC-2026-2027-0001', 'inscription_id' => 1, 'frais_scolaire_id' => 1,
        'montant_paye' => 60, 'date_paiement' => now(), 'mode_paiement' => 'especes', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $paiementId = DB::table('paiements')->value('id');
    $comptable = User::factory()->create(['role' => 'comptable']);

    // Actives par défaut.
    $this->actingAs($comptable)->get(route('comptable.recouvrement.index'))->assertOk();
    $this->actingAs($comptable)->get(route('comptable.paiements.recu', $paiementId))->assertOk();

    // Désactivées : les pages sont fermées (404).
    DB::table('parametres_systeme')->update(['module_recus_actif' => 0, 'module_recouvrement_actif' => 0]);

    $this->actingAs($comptable)->get(route('comptable.recouvrement.index'))->assertNotFound();
    $this->actingAs($comptable)->get(route('comptable.paiements.recu', $paiementId))->assertNotFound();
});
