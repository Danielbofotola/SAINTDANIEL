<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $tables = [
        'paiements', 'frais_scolaires', 'types_frais', 'affectations_cours', 'cours',
        'inscriptions', 'eleves', 'classes', 'cycles', 'salles', 'professeurs', 'annees_scolaires', 'users',
    ];

    foreach ($tables as $table) {
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

    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('matricule');
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('specialite')->nullable();
        $table->string('photo')->nullable();
        $table->string('statut')->default('actif');
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

    Schema::create('cours', function (Blueprint $table) {
        $table->id();
        $table->string('code');
        $table->string('intitule');
    });

    Schema::create('affectations_cours', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id');
        $table->unsignedBigInteger('cours_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('types_frais', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
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
        $table->string('num_recu')->unique();
        $table->unsignedBigInteger('inscription_id');
        $table->unsignedBigInteger('frais_scolaire_id');
        $table->decimal('montant_paye', 10, 2);
        $table->dateTime('date_paiement');
        $table->string('mode_paiement')->default('especes');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    // Année en cours
    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-01',
        'is_encours' => true, 'statut' => 'actif',
    ]);

    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Primaire', 'statut' => 'actif']);
    DB::table('salles')->insert(['id' => 1, 'nom' => 'Salle A']);
    DB::table('classes')->insert([
        'id' => 1, 'nom' => '3e primaire', 'cycle_id' => 1, 'salle_id' => 1, 'statut' => 'actif',
    ]);

    DB::table('eleves')->insert([
        ['id' => 1, 'matricule' => 'ELV-001', 'nom' => 'Banzolo', 'postnom' => 'Bofotola', 'prenom' => 'Daniel', 'sexe' => 'M', 'date_naissance' => '2017-05-12', 'statut' => 'actif'],
        ['id' => 2, 'matricule' => 'ELV-002', 'nom' => 'Bokonde', 'postnom' => 'Nsombola', 'prenom' => 'Annie', 'sexe' => 'F', 'date_naissance' => '2016-08-23', 'statut' => 'actif'],
    ]);

    DB::table('inscriptions')->insert([
        ['id' => 1, 'eleve_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-05', 'statut' => 'actif'],
        ['id' => 2, 'eleve_id' => 2, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-06', 'statut' => 'actif'],
    ]);

    DB::table('types_frais')->insert(['id' => 1, 'nom' => 'Minerval']);
    DB::table('frais_scolaires')->insert([
        ['id' => 1, 'type_frais_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'montant_exige' => 100, 'date_echeance' => '2026-10-05', 'statut' => 'actif'],
        ['id' => 2, 'type_frais_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'montant_exige' => 100, 'date_echeance' => '2026-11-05', 'statut' => 'actif'],
    ]);
    DB::table('paiements')->insert([
        'id' => 1, 'num_recu' => 'REC-0001', 'inscription_id' => 1, 'frais_scolaire_id' => 1,
        'montant_paye' => 50, 'date_paiement' => '2026-10-02 09:30:00', 'mode_paiement' => 'especes', 'statut' => 'actif',
    ]);

    // Titulaire : professeur lié + une affectation sur la classe 1
    DB::table('professeurs')->insert([
        'id' => 1, 'matricule' => 'PRF-001', 'nom' => 'Mwamba', 'postnom' => 'Kabila', 'prenom' => 'Jean',
        'specialite' => 'Mathématiques', 'statut' => 'actif',
    ]);
    DB::table('cours')->insert(['id' => 1, 'code' => 'MATH6', 'intitule' => 'Mathématiques 6']);
    DB::table('affectations_cours')->insert([
        'id' => 1, 'professeur_id' => 1, 'cours_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'statut' => 'actif',
    ]);
});

test('the accountant dashboard shows the current year finances', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->get(route('comptable.dashboard'))
        ->assertOk()
        ->assertSee('Espace comptable')
        ->assertSee('2026-2027')
        ->assertSee('Recouvrement par classe')
        ->assertSee('3e primaire')
        ->assertSee('25 %')      // 50 encaissés sur 200 définis
        ->assertSee('Reste à percevoir')
        ->assertSee('REC-0001');
});

test('the accountant dashboard warns when no school year is current', function () {
    DB::table('annees_scolaires')->update(['is_encours' => false]);
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->get(route('comptable.dashboard'))
        ->assertOk()
        ->assertSee('Aucune année scolaire en cours');
});

test('the homeroom dashboard shows the classes and pupils of the current year', function () {
    $titulaire = User::factory()->create(['role' => 'titulaire', 'professeur_id' => 1]);

    $this->actingAs($titulaire)
        ->get(route('titulaire.dashboard'))
        ->assertOk()
        ->assertSee('Espace titulaire')
        ->assertSee('3e primaire')
        ->assertSee('2 élève(s)')
        ->assertSee('Banzolo')
        ->assertSee('Annie');
});

test('the homeroom dashboard warns when the account is not linked to a teacher record', function () {
    $titulaire = User::factory()->create(['role' => 'titulaire', 'professeur_id' => null]);

    $this->actingAs($titulaire)
        ->get(route('titulaire.dashboard'))
        ->assertOk()
        ->assertSee('Aucune fiche enseignant');
});

test('each role space is closed to the other roles', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);
    $titulaire = User::factory()->create(['role' => 'titulaire', 'professeur_id' => 1]);

    $this->actingAs($comptable)->get(route('titulaire.dashboard'))->assertForbidden();
    $this->actingAs($titulaire)->get(route('comptable.dashboard'))->assertForbidden();
    $this->actingAs($titulaire)->get(route('professeur.dashboard'))->assertForbidden();
});
