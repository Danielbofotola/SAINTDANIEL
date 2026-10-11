<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach (['paies', 'salaires', 'professeurs', 'users'] as $table) {
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
        $table->string('photo')->nullable();
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
        $table->unique(['beneficiaire_type', 'beneficiaire_id']);
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
        $table->unique(['salaire_id', 'periode']);
    });

    DB::table('professeurs')->insert([
        'id' => 1, 'matricule' => 'PROF-2026-0001', 'nom' => 'Kabila', 'postnom' => 'Mwamba', 'prenom' => 'Aline',
        'sexe' => 'F', 'specialite' => 'Mathématiques', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
});

test('an admin defines a salary and pays a month with primes and deductions', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.paies.salaires.store'), [
            'beneficiaire_type' => 'professeur', 'beneficiaire_id' => 1, 'salaire_base' => 200,
        ])
        ->assertRedirect();

    $salaire = DB::table('salaires')->first();
    expect($salaire)->not->toBeNull()
        ->and($salaire->beneficiaire_type)->toBe('professeur')
        ->and((int) $salaire->beneficiaire_id)->toBe(1)
        ->and((float) $salaire->salaire_base)->toBe(200.0);

    $this->actingAs($admin)
        ->post(route('admin.paies.store'), [
            'salaire_id' => $salaire->id, 'periode' => '2026-10',
            'primes' => 20, 'retenues' => 5,
            'date_paiement' => '2026-10-05', 'mode_paiement' => 'banque',
        ])
        ->assertRedirect();

    $paie = DB::table('paies')->first();
    expect($paie)->not->toBeNull()
        ->and($paie->num_paie)->toBe('PAIE-2026-10-0001')
        ->and((float) $paie->montant_base)->toBe(200.0)
        ->and((float) $paie->primes)->toBe(20.0)
        ->and((float) $paie->retenues)->toBe(5.0)
        ->and((float) $paie->montant_net)->toBe(215.0);
});

test('the same month cannot be paid twice for the same person', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->post(route('admin.paies.salaires.store'), [
        'beneficiaire_type' => 'professeur', 'beneficiaire_id' => 1, 'salaire_base' => 100,
    ]);
    $salaireId = DB::table('salaires')->value('id');

    $charge = [
        'salaire_id' => $salaireId, 'periode' => '2026-10',
        'date_paiement' => '2026-10-05', 'mode_paiement' => 'especes',
    ];

    $this->actingAs($admin)->post(route('admin.paies.store'), $charge)->assertRedirect();
    $this->actingAs($admin)->post(route('admin.paies.store'), $charge)->assertSessionHas('error');

    expect(DB::table('paies')->count())->toBe(1);
});

test('the cashier can pay a salary and another staff member can be registered', function () {
    $comptable = User::factory()->create(['role' => 'comptable', 'nom' => 'Ilunga', 'postnom' => 'K', 'prenom' => 'Paul']);
    $caissier = User::factory()->create(['role' => 'comptable', 'nom' => 'Mwamba', 'postnom' => 'T', 'prenom' => 'Jeanne']);

    $this->actingAs($caissier)
        ->get(route('comptable.paies.index'))
        ->assertOk()
        ->assertSee('Kabila')
        ->assertSee('Ilunga');

    // Un membre du personnel (compte utilisateur) reçoit un salaire.
    $this->actingAs($caissier)
        ->post(route('comptable.paies.salaires.store'), [
            'beneficiaire_type' => 'utilisateur', 'beneficiaire_id' => $comptable->id, 'salaire_base' => 150,
        ])
        ->assertRedirect();

    $salaireId = DB::table('salaires')->where('beneficiaire_type', 'utilisateur')->value('id');

    $this->actingAs($caissier)
        ->post(route('comptable.paies.store'), [
            'salaire_id' => $salaireId, 'periode' => '2026-11',
            'date_paiement' => '2026-11-05', 'mode_paiement' => 'especes',
        ])
        ->assertRedirect();

    expect((float) DB::table('paies')->where('periode', '2026-11')->value('montant_net'))->toBe(150.0);
});

test('a professor account cannot access the payroll pages', function () {
    $professeur = User::factory()->create(['role' => 'professeur']);

    $this->actingAs($professeur)->get(route('admin.paies.index'))->assertForbidden();
    $this->actingAs($professeur)->get(route('comptable.paies.index'))->assertForbidden();
});
