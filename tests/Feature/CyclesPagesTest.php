<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'inscriptions', 'eleves', 'tuteurs', 'classes', 'cycles', 'salles',
        'professeurs', 'annees_scolaires', 'users',
    ] as $table) {
        Schema::dropIfExists($table);
    }
    Schema::enableForeignKeyConstraints();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable()->unique();
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
        $table->string('statut')->default('actif');
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
        $table->string('nom', 50)->unique();
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });
    Schema::create('salles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->unsignedInteger('capacite')->nullable();
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

    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif',
    ]);
});

test('an admin can list, create and toggle cycles', function () {
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Primaire', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'nom' => '1ère', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.cycles.index'))
        ->assertOk()
        ->assertSee('Cycles')
        ->assertSee('Primaire');

    $this->actingAs($admin)
        ->post(route('admin.cycles.store'), ['nom' => 'Secondaire'])
        ->assertRedirect(route('admin.cycles.index'));
    $this->assertDatabaseHas('cycles', ['nom' => 'Secondaire', 'statut' => 'actif']);

    $this->actingAs($admin)
        ->post(route('admin.cycles.store'), ['nom' => 'Secondaire'])
        ->assertSessionHasErrors('nom');

    $this->actingAs($admin)
        ->patch(route('admin.cycles.toggle-status', 1))
        ->assertRedirect();
    expect(DB::table('cycles')->where('id', 1)->value('statut'))->toBe('inactif');
});

test('a cycle with classes cannot be deleted', function () {
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Primaire', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'nom' => '1ère', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('cycles')->insert(['id' => 2, 'nom' => 'Secondaire', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->delete(route('admin.cycles.destroy', 1))
        ->assertSessionHas('error');
    expect(DB::table('cycles')->count())->toBe(2);

    $this->actingAs($admin)
        ->delete(route('admin.cycles.destroy', 2))
        ->assertRedirect(route('admin.cycles.index'));
    expect(DB::table('cycles')->count())->toBe(1);
});

test('the cycles menu link points to the cycles page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.cycles.index'), false);
});
