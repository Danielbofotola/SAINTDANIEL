<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    // La page des dépenses calcule aussi le signal de charge : il lui faut les
    // paiements (recettes) et les paies (salaires), en plus des dépenses.
    foreach (['paies', 'salaires', 'professeurs', 'paiements', 'frais_scolaires', 'types_frais', 'inscriptions', 'eleves', 'tuteurs', 'classes', 'cycles', 'depenses', 'categories_depenses', 'annees_scolaires', 'users'] as $table) {
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
        $table->string('telephone', 20)->nullable();
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

    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif',
    ]);
    // Tables lues par le calcul du signal de charge (recettes et salaires).
    Schema::create('inscriptions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->date('date_inscription');
        $table->string('statut');
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
    DB::table('categories_depenses')->insert([
        'id' => 1, 'nom' => 'Fournitures', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
});

test('the cashier records an expense together with its nature', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->get(route('comptable.depenses.index'))
        ->assertOk()
        ->assertSee('Enregistrer une dépense')
        ->assertSee('Nature de la dépense')
        ->assertSee('Fournitures');

    $this->actingAs($comptable)
        ->post(route('comptable.depenses.store'), [
            'nature_id' => 1,
            'motif' => 'Achat de craies',
            'montant' => 45.5,
            'date_depense' => '2026-10-04',
            'piece_justificative' => 'FACT-001',
        ])
        ->assertRedirect(route('comptable.depenses.index'))
        ->assertSessionHas('success', fn (string $message) => str_contains($message, 'Fournitures'));

    $depense = DB::table('depenses')->first();
    expect($depense)->not->toBeNull()
        ->and((int) $depense->category_id)->toBe(1)
        ->and($depense->motif)->toBe('Achat de craies')
        ->and((float) $depense->montant)->toBe(45.5)
        ->and((int) $depense->annee_scolaire_id)->toBe(1);
});

test('the cashier can create a new expense nature', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->post(route('comptable.depenses.natures.store'), ['nom' => 'Entretien'])
        ->assertRedirect();

    expect(DB::table('categories_depenses')->where('nom', 'Entretien')->count())->toBe(1);
});

test('an expense without a nature is rejected', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->post(route('comptable.depenses.store'), [
            'nature_id' => '', 'motif' => 'Sans nature', 'montant' => 10, 'date_depense' => '2026-10-04',
        ])
        ->assertSessionHasErrors('nature_id');

    expect(DB::table('depenses')->count())->toBe(0);
});

test('the cashier can delete an expense entry', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);
    DB::table('depenses')->insert([
        'annee_scolaire_id' => 1, 'category_id' => 1, 'motif' => 'Erreur de saisie',
        'montant' => 5, 'date_depense' => '2026-10-04', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $depenseId = DB::table('depenses')->value('id');

    $this->actingAs($comptable)
        ->delete(route('comptable.depenses.destroy', $depenseId))
        ->assertRedirect();

    expect(DB::table('depenses')->count())->toBe(0);
});

test('the expense page is restricted to the cashier role', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('comptable.depenses.index'))
        ->assertForbidden();
});
