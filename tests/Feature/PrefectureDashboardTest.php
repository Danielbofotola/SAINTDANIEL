<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    foreach (['notifications', 'documents_professeurs', 'inscriptions', 'eleves', 'classes', 'cycles', 'sections', 'options', 'salles', 'professeurs', 'annees_scolaires', 'users'] as $table) {
        Schema::dropIfExists($table);
    }

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable();
        $table->string('nom', 50)->nullable();
        $table->string('prenom', 50)->nullable();
        $table->string('username', 50)->unique();
        $table->string('email', 100)->unique();
        $table->string('photo')->nullable();
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

    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('sections', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->timestamps();
    });

    Schema::create('options', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('section_id');
        $table->string('nom');
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

    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('matricule');
        $table->string('nom');
        $table->string('prenom');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('documents_professeurs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id');
        $table->string('titre');
        $table->string('type_document')->default('autre');
        $table->string('chemin_fichier');
        $table->integer('taille_ko')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('notifications', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('type');
        $table->morphs('notifiable');
        $table->text('data');
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
    });

    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-01',
        'is_encours' => true, 'statut' => 'actif',
    ]);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Primaire', 'statut' => 'actif']);
    DB::table('salles')->insert(['id' => 1, 'nom' => 'Salle A']);
    DB::table('classes')->insert(['id' => 1, 'nom' => '3e primaire', 'cycle_id' => 1, 'salle_id' => 1, 'statut' => 'actif']);
    DB::table('sections')->insert(['id' => 1, 'nom' => 'Scientifique']);
    DB::table('options')->insert(['id' => 1, 'section_id' => 1, 'nom' => 'Latin-Mathématiques']);
    DB::table('professeurs')->insert(['id' => 1, 'matricule' => 'PRF-001', 'nom' => 'Mwamba', 'prenom' => 'Jean', 'statut' => 'actif']);
    DB::table('eleves')->insert(['id' => 1, 'matricule' => 'ELV-001', 'nom' => 'Banzolo', 'prenom' => 'Daniel', 'sexe' => 'M', 'date_naissance' => '2017-05-12', 'statut' => 'actif']);
    DB::table('inscriptions')->insert(['id' => 1, 'eleve_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-05', 'statut' => 'actif']);

    User::factory()->create(['role' => 'prefetture']);
    User::factory()->create(['role' => 'comptable']);
});

test('the prefecture space is a dedicated page, not the shared placeholder', function () {
    $prefet = User::where('role', 'prefetture')->first();

    $this->actingAs($prefet)
        ->get(route('prefecture.dashboard'))
        ->assertOk()
        ->assertSee('Espace préfecture')
        ->assertSee('Effectifs par cycle')
        ->assertSee('Primaire')
        ->assertSee('3e primaire')
        ->assertSee('Personnel')
        ->assertSee('Sections')
        ->assertSee('2026-2027')
        ->assertDontSee('Voir mon profil et mes coordonnées');
});

test('the prefecture space reports the current year figures only', function () {
    // Un élève inscrit sur une autre année ne doit pas compter
    DB::table('annees_scolaires')->insert([
        'id' => 2, 'libelle' => '2025-2026', 'date_debut' => '2025-09-01', 'date_fin' => '2026-07-01',
        'is_encours' => false, 'statut' => 'actif',
    ]);
    DB::table('eleves')->insert(['id' => 2, 'matricule' => 'ELV-002', 'nom' => 'Ancien', 'prenom' => 'Eleve', 'sexe' => 'F', 'date_naissance' => '2015-01-01', 'statut' => 'actif']);
    DB::table('inscriptions')->insert(['id' => 2, 'eleve_id' => 2, 'classe_id' => 1, 'annee_scolaire_id' => 2, 'date_inscription' => '2025-09-05', 'statut' => 'actif']);

    $prefet = User::where('role', 'prefetture')->first();

    $this->actingAs($prefet)
        ->get(route('prefecture.dashboard'))
        ->assertOk()
        // 1 seul élève de l'année en cours : la répartition du cycle le confirme.
        ->assertSee('1 (100%)');
});

test('the prefecture space is closed to the other roles', function () {
    $comptable = User::where('role', 'comptable')->first();

    $this->actingAs($comptable)
        ->get(route('prefecture.dashboard'))
        ->assertForbidden();
});

test('the shared placeholder page has been retired', function () {
    expect(view()->exists('dashboard'))->toBeFalse();
});

test('chaque rubrique de l’espace préfecture dispose de sa propre page', function () {
    $prefet = User::where('role', 'prefetture')->first();

    $this->actingAs($prefet)->get(route('prefecture.effectifs'))->assertOk()
        ->assertSee('Effectifs par cycle')->assertSee('Garçons')->assertSee('Filles');

    $this->actingAs($prefet)->get(route('prefecture.classes'))->assertOk()
        ->assertSee('Organisation des classes')->assertSee('3e primaire')->assertSee('Salle A');

    $this->actingAs($prefet)->get(route('prefecture.personnel'))->assertOk()
        ->assertSee('Comptes par rôle')->assertSee('Enseignants')->assertSee('Mwamba');

    // Le tableau de bord renvoie vers ces pages.
    $this->actingAs($prefet)->get(route('prefecture.dashboard'))->assertOk()
        ->assertSee(route('prefecture.effectifs'), false)
        ->assertSee(route('prefecture.classes'), false)
        ->assertSee(route('prefecture.personnel'), false);
});

test('le profil du préfet expose son identité, son compte et son périmètre', function () {
    $prefet = User::where('role', 'prefetture')->first();
    $prefet->update(['nom' => 'Kalala', 'prenom' => 'Pierre']);

    $this->actingAs($prefet)
        ->get(route('prefecture.profil'))
        ->assertOk()
        ->assertSee('Kalala')
        ->assertSee('Identité')
        ->assertSee('Compte')
        ->assertSee('Dernière connexion')
        ->assertSee('Périmètre du rôle')
        ->assertSee('coup d')
        ->assertSee($prefet->username);
});

test('les pages de l’espace préfecture sont fermées aux autres rôles', function () {
    $comptable = User::where('role', 'comptable')->first();

    foreach (['prefecture.effectifs', 'prefecture.classes', 'prefecture.personnel', 'prefecture.profil'] as $route) {
        $this->actingAs($comptable)->get(route($route))->assertForbidden();
    }
});
