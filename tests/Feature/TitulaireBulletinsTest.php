<?php

use App\Models\Bulletin;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Espace titulaire — bulletins & synthèse de classe : accès limité à ses
 * classes, génération des bulletins, appréciations, document RDC.
 */
beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'bulletins', 'notes', 'evaluations', 'periodes', 'affectations_cours', 'cours',
        'inscriptions', 'eleves', 'classes', 'cycles', 'salles', 'professeurs',
        'annees_scolaires', 'users',
    ] as $table) {
        Schema::dropIfExists($table);
    }
    Schema::enableForeignKeyConstraints();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable();
        $table->string('nom', 50)->nullable();
        $table->string('postnom', 50)->nullable();
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
        $table->unsignedInteger('ponderation')->default(1);
        $table->unsignedInteger('max_travaux_journaliers')->nullable();
        $table->unsignedInteger('max_examen')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
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
    Schema::create('periodes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('nom');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('evaluations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
        $table->unsignedBigInteger('periode_id');
        $table->string('titre');
        $table->string('type_evaluation')->default('interro');
        $table->decimal('max_note', 5, 2)->default(20);
        $table->date('date_evaluation')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('notes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('evaluation_id');
        $table->unsignedBigInteger('eleve_id');
        $table->decimal('note_obtenue', 5, 2)->nullable();
        $table->string('observation')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('bulletins', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('inscription_id');
        $table->unsignedBigInteger('periode_id');
        $table->decimal('total_points', 7, 2)->default(0);
        $table->decimal('max_possible', 7, 2)->default(0);
        $table->decimal('pourcentage', 5, 2)->default(0);
        $table->unsignedInteger('place_classe')->nullable();
        $table->string('mention', 50)->nullable();
        $table->string('application', 50)->nullable();
        $table->string('conduite', 50)->nullable();
        $table->string('chemin_pdf')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    // Année en cours, cycle, classes (1 = la sienne, 2 = celle d'un autre).
    DB::table('annees_scolaires')->insert(['id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif']);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Primaire', 'statut' => 'actif']);
    DB::table('salles')->insert(['id' => 1, 'nom' => 'Salle A']);
    DB::table('classes')->insert([
        ['id' => 1, 'nom' => '3e primaire', 'cycle_id' => 1, 'salle_id' => 1, 'statut' => 'actif'],
        ['id' => 2, 'nom' => 'Classe interdite', 'cycle_id' => 1, 'salle_id' => null, 'statut' => 'actif'],
    ]);

    DB::table('eleves')->insert([
        ['id' => 1, 'matricule' => 'ELV-001', 'nom' => 'Banzolo', 'postnom' => 'Bofotola', 'prenom' => 'Daniel', 'sexe' => 'M', 'date_naissance' => '2017-05-12', 'statut' => 'actif'],
        ['id' => 2, 'matricule' => 'ELV-002', 'nom' => 'Bokonde', 'postnom' => 'Nsombola', 'prenom' => 'Annie', 'sexe' => 'F', 'date_naissance' => '2016-08-23', 'statut' => 'actif'],
        ['id' => 3, 'matricule' => 'ELV-003', 'nom' => 'Autre', 'postnom' => 'Classe', 'prenom' => 'Paul', 'sexe' => 'M', 'date_naissance' => '2016-01-01', 'statut' => 'actif'],
    ]);
    DB::table('inscriptions')->insert([
        ['id' => 1, 'eleve_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-05', 'statut' => 'actif'],
        ['id' => 2, 'eleve_id' => 2, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-06', 'statut' => 'actif'],
        ['id' => 3, 'eleve_id' => 3, 'classe_id' => 2, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-07', 'statut' => 'actif'],
    ]);

    DB::table('professeurs')->insert(['id' => 1, 'matricule' => 'PRF-001', 'nom' => 'Mwamba', 'postnom' => 'Kabila', 'prenom' => 'Jean', 'specialite' => 'Mathématiques', 'statut' => 'actif']);
    DB::table('cours')->insert(['id' => 1, 'code' => 'MATH6', 'intitule' => 'Mathématiques 6', 'max_travaux_journaliers' => 20, 'max_examen' => 20, 'statut' => 'actif']);
    DB::table('affectations_cours')->insert(['id' => 1, 'professeur_id' => 1, 'cours_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'statut' => 'actif']);
    DB::table('periodes')->insert(['id' => 1, 'annee_scolaire_id' => 1, 'nom' => '1er trimestre', 'statut' => 'actif']);
    DB::table('evaluations')->insert(['id' => 1, 'affectation_id' => 1, 'periode_id' => 1, 'titre' => 'Interro 1', 'type_evaluation' => 'interro', 'max_note' => 20, 'statut' => 'actif']);
    DB::table('notes')->insert([
        ['evaluation_id' => 1, 'eleve_id' => 1, 'note_obtenue' => 15, 'statut' => 'actif'],
        ['evaluation_id' => 1, 'eleve_id' => 2, 'note_obtenue' => 10, 'statut' => 'actif'],
    ]);
});

test('le titulaire voit le classement de sa classe', function () {
    $titulaire = User::factory()->create(['role' => 'titulaire', 'professeur_id' => 1]);

    $this->actingAs($titulaire)
        ->get(route('titulaire.bulletins.index', ['periode_id' => 1, 'classe_id' => 1]))
        ->assertOk()
        ->assertSee('Bulletins')
        ->assertSee('3e primaire')
        ->assertSee('Banzolo')
        ->assertSee('75.00 %');   // 15/20
});

test('le titulaire ne peut pas consulter une classe qui n’est pas la sienne', function () {
    $titulaire = User::factory()->create(['role' => 'titulaire', 'professeur_id' => 1]);

    $this->actingAs($titulaire)
        ->get(route('titulaire.bulletins.index', ['periode_id' => 1, 'classe_id' => 2]))
        ->assertOk()
        ->assertSee('Choisissez une période et une classe')
        ->assertDontSee('Classe interdite');
});

test('le titulaire génère les bulletins de sa classe', function () {
    $titulaire = User::factory()->create(['role' => 'titulaire', 'professeur_id' => 1]);

    $this->actingAs($titulaire)->post(route('titulaire.bulletins.generate'), [
        'periode_id' => 1, 'classe_id' => 1,
    ])->assertRedirect()->assertSessionHas('success');

    expect(Bulletin::where('periode_id', 1)->count())->toBe(2)
        ->and(Bulletin::where('inscription_id', 1)->value('place_classe'))->toBe(1)
        ->and((float) Bulletin::where('inscription_id', 1)->value('pourcentage'))->toBe(75.0);
});

test('le titulaire enregistre les appréciations de conduite et d’application', function () {
    $titulaire = User::factory()->create(['role' => 'titulaire', 'professeur_id' => 1]);

    $this->actingAs($titulaire)->post(route('titulaire.bulletins.appreciations'), [
        'periode_id' => 1, 'classe_id' => 1,
        'conduite' => [1 => 'Bonne', 2 => 'Passable'],
        'application' => [1 => 'Régulier'],
    ])->assertRedirect()->assertSessionHas('success');

    expect(Bulletin::where('inscription_id', 1)->value('conduite'))->toBe('Bonne')
        ->and(Bulletin::where('inscription_id', 1)->value('application'))->toBe('Régulier')
        ->and(Bulletin::where('inscription_id', 2)->value('conduite'))->toBe('Passable');
});

test('le titulaire refuse un contexte qui n’est pas sa classe', function () {
    $titulaire = User::factory()->create(['role' => 'titulaire', 'professeur_id' => 1]);

    $this->actingAs($titulaire)->post(route('titulaire.bulletins.generate'), [
        'periode_id' => 1, 'classe_id' => 2,
    ])->assertRedirect()->assertSessionHas('error');

    expect(Bulletin::count())->toBe(0);
});

test('le document RDC est accessible pour sa classe, interdit pour une autre', function () {
    $titulaire = User::factory()->create(['role' => 'titulaire', 'professeur_id' => 1]);

    $this->actingAs($titulaire)->get(route('titulaire.bulletins.document', 1))->assertOk();
    $this->actingAs($titulaire)->get(route('titulaire.bulletins.document', 3))->assertForbidden();
});

test('l’espace titulaire bulletins est réservé au rôle titulaire', function () {
    $professeur = User::factory()->create(['role' => 'professeur', 'professeur_id' => 1]);

    $this->actingAs($professeur)
        ->get(route('titulaire.bulletins.index'))
        ->assertForbidden();
});
