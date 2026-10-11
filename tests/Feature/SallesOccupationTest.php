<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach (['inscriptions', 'eleves', 'classes', 'cycles', 'salles', 'annees_scolaires', 'users'] as $table) {
        Schema::dropIfExists($table);
    }
    Schema::enableForeignKeyConstraints();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable()->unique();
        $table->string('nom', 50)->nullable();
        $table->string('postnom', 50)->nullable();
        $table->string('prenom', 50)->nullable();
        $table->string('telephone', 20)->nullable();
        $table->text('adresse')->nullable();
        $table->string('photo')->nullable();
        $table->string('username')->unique();
        $table->string('email')->unique();
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
    Schema::create('eleves', function (Blueprint $table) {
        $table->id();
        $table->string('matricule')->unique();
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('sexe');
        $table->date('date_naissance')->nullable();
        $table->unsignedBigInteger('tuteur_id')->nullable();
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('inscriptions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->date('date_inscription');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif',
    ]);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Primaire', 'statut' => 'actif']);
});

function seedSalle(string $nom, ?int $capacite): int
{
    return DB::table('salles')->insertGetId([
        'nom' => $nom, 'capacite' => $capacite, 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

function seedClasseDansSalle(int $salleId): int
{
    return DB::table('classes')->insertGetId([
        'cycle_id' => 1, 'salle_id' => $salleId, 'nom' => 'Classe '.$salleId, 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

function inscrireEleve(int $classeId, int $numero): void
{
    $eleveId = DB::table('eleves')->insertGetId([
        'matricule' => 'ELV-'.$numero, 'nom' => 'Eleve'.$numero, 'prenom' => 'Test',
        'sexe' => 'M', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('inscriptions')->insert([
        'eleve_id' => $eleveId, 'classe_id' => $classeId, 'annee_scolaire_id' => 1,
        'date_inscription' => '2026-09-05', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

test('une salle proche de sa capacité est signalée', function () {
    $salle = seedSalle('Salle A', 2);
    $classe = seedClasseDansSalle($salle);
    inscrireEleve($classe, 1);
    inscrireEleve($classe, 2);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.salles.index'))
        ->assertOk()
        ->assertSee('Locaux à surveiller')
        ->assertSee('Salle A — 2/2 (100 %)')
        ->assertSee('Presque saturée');
});

test('une salle au-delà de sa capacité est signalée comme saturée', function () {
    $salle = seedSalle('Salle B', 1);
    $classe = seedClasseDansSalle($salle);
    inscrireEleve($classe, 1);
    inscrireEleve($classe, 2);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.salles.index'))
        ->assertOk()
        ->assertSee('Salle B — 2/1 (200 %)')
        ->assertSee('Saturée');
});

test('une salle sans capacité n’est pas signalée', function () {
    $salle = seedSalle('Salle C', null);
    $classe = seedClasseDansSalle($salle);
    inscrireEleve($classe, 1);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.salles.index'))
        ->assertOk()
        ->assertDontSee('Locaux à surveiller')
        ->assertSee('Capacité non définie');
});
