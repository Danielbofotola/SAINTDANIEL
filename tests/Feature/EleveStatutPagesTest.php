<?php

use App\Models\Eleve;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Statuts d'élève : désactivés (page dédiée, exclus partout) et archivés
 * (diplômés / renvoyés), qui ne figurent plus dans la liste des élèves actifs.
 */
beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'certifications', 'inscriptions', 'eleves', 'tuteurs', 'classes', 'cycles',
        'salles', 'sections', 'options', 'annees_scolaires', 'users',
    ] as $table) {
        Schema::dropIfExists($table);
    }
    Schema::enableForeignKeyConstraints();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable();
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
        $table->string('libelle', 20)->unique();
        $table->date('date_debut');
        $table->date('date_fin');
        $table->boolean('is_encours')->default(false);
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('cycles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('statut')->default('actif');
    });
    Schema::create('salles', function (Blueprint $table) {
        $table->id();
        $table->string('nom')->nullable();
    });
    Schema::create('sections', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
    });
    Schema::create('options', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('section_id')->nullable();
        $table->string('nom');
    });
    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->unsignedBigInteger('section_id')->nullable();
        $table->unsignedBigInteger('option_id')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('tuteurs', function (Blueprint $table) {
        $table->id();
        $table->string('nom_pere')->nullable();
        $table->string('nom_mere')->nullable();
        $table->timestamps();
    });
    Schema::create('eleves', function (Blueprint $table) {
        $table->id();
        $table->string('matricule')->unique();
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('sexe')->nullable();
        $table->date('date_naissance')->nullable();
        $table->unsignedBigInteger('tuteur_id')->nullable();
        $table->string('statut')->default('actif');
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
    Schema::create('certifications', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('libelle', 100);
        $table->date('date_obtention');
        $table->timestamps();
    });

    DB::table('annees_scolaires')->insert(['id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif']);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Primaire', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'nom' => '3e primaire', 'cycle_id' => 1, 'statut' => 'actif']);
    DB::table('tuteurs')->insert(['id' => 1, 'nom_mere' => 'Mama', 'created_at' => now(), 'updated_at' => now()]);

    // Quatre élèves, un par famille de statut, tous inscrits cette année.
    DB::table('eleves')->insert([
        ['id' => 1, 'matricule' => 'ELV-001', 'nom' => 'NomActif', 'prenom' => 'Alice', 'sexe' => 'F', 'tuteur_id' => 1, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'matricule' => 'ELV-002', 'nom' => 'NomDesactive', 'prenom' => 'Bruno', 'sexe' => 'M', 'tuteur_id' => 1, 'statut' => 'inactif', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 3, 'matricule' => 'ELV-003', 'nom' => 'NomDiplome', 'prenom' => 'Carla', 'sexe' => 'F', 'tuteur_id' => 1, 'statut' => 'diplome', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 4, 'matricule' => 'ELV-004', 'nom' => 'NomExclu', 'prenom' => 'David', 'sexe' => 'M', 'tuteur_id' => 1, 'statut' => 'exclu', 'created_at' => now(), 'updated_at' => now()],
    ]);
    foreach ([1, 2, 3, 4] as $eleveId) {
        DB::table('inscriptions')->insert(['eleve_id' => $eleveId, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-05', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    }
});

test('la liste principale n’affiche ni les désactivés ni les archivés', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.eleves.index'))
        ->assertOk()
        ->assertSee('NomActif')
        ->assertDontSee('NomDesactive')
        ->assertDontSee('NomDiplome')
        ->assertDontSee('NomExclu');
});

test('la page des désactivés n’affiche que les élèves désactivés', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.eleves.desactives'))
        ->assertOk()
        ->assertSee('NomDesactive')
        ->assertDontSee('NomActif')
        ->assertDontSee('NomDiplome');
});

test('la page des archives range diplômés et renvoyés', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.eleves.archives'))
        ->assertOk()
        ->assertSee('NomDiplome')
        ->assertSee('NomExclu')
        ->assertSee('Renvoyé')
        ->assertDontSee('NomActif')
        ->assertDontSee('NomDesactive');
});

test('un élève désactivé disparaît de la liste de sa classe', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.classes.students', 1))
        ->assertOk()
        ->assertSee('NomActif')
        ->assertDontSee('NomDesactive');
});

test('réactiver un désactivé le ramène dans la liste active', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $desactive = Eleve::findOrFail(2);

    $this->actingAs($admin)->patch(route('admin.eleves.toggle', $desactive))->assertRedirect();

    expect($desactive->fresh()->statut)->toBe('actif');

    $this->actingAs($admin)->get(route('admin.eleves.index'))
        ->assertOk()
        ->assertSee('NomDesactive');
});
