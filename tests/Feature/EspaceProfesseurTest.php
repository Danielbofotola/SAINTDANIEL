<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'presences', 'notes', 'evaluations', 'periodes', 'emplois_du_temps', 'affectations_cours',
        'inscriptions', 'eleves', 'tuteurs', 'cours', 'classes', 'salles', 'sections', 'options',
        'cycles', 'professeurs', 'annees_scolaires', 'users',
        'parametres_systeme',
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
        $table->string('photo')->nullable();
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
        $table->string('photo')->nullable();
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
    Schema::create('sections', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('options', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('salles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->unsignedInteger('capacite')->nullable();
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('cycle_id');
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->string('nom');
        $table->string('section_id')->nullable();
        $table->string('option_id')->nullable();
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('cours', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('intitule');
        $table->unsignedInteger('ponderation')->default(1);
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
        $table->string('photo')->nullable();
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
    Schema::create('affectations_cours', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id');
        $table->unsignedBigInteger('cours_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('emplois_du_temps', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->string('jour');
        $table->time('heure_debut');
        $table->time('heure_fin');
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('periodes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('nom');
        $table->string('statut');
        $table->timestamps();
    });
    Schema::create('evaluations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
        $table->unsignedBigInteger('periode_id');
        $table->string('titre');
        $table->string('type_evaluation')->default('interro');
        $table->decimal('max_note', 5, 2)->default(20);
        $table->date('date_evaluation');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('notes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('evaluation_id');
        $table->unsignedBigInteger('eleve_id');
        $table->decimal('note_obtenue', 5, 2);
        $table->string('observation')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('presences', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
        $table->unsignedBigInteger('eleve_id');
        $table->date('date_seance');
        $table->string('statut_presence')->default('present');
        $table->string('motif', 200)->nullable();
        $table->unsignedBigInteger('enregistre_par')->nullable();
        $table->timestamps();
        $table->unique(['affectation_id', 'eleve_id', 'date_seance']);
    });

    // Jeu de données : un professeur, une classe, deux élèves, un cours affecté.
    Schema::create('parametres_systeme', function (Blueprint $table) {
        $table->id();
        $table->string('nom_etablissement', 150)->default('Etablissement');
        $table->string('nom_court', 60)->default('Ecole');
        $table->string('telephone', 30)->nullable();
        $table->string('email', 150)->nullable();
        $table->text('adresse')->nullable();
        $table->timestamps();
    });
    DB::table('parametres_systeme')->insert(['nom_court' => 'Ecole Test', 'created_at' => now(), 'updated_at' => now()]);

    DB::table('annees_scolaires')->insert(['id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif']);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'nom' => '3e A', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 2, 'cycle_id' => 1, 'nom' => '4e B', 'statut' => 'actif']);
    DB::table('salles')->insert(['id' => 1, 'nom' => 'Salle 12', 'statut' => 'actif']);
    DB::table('cours')->insert(['id' => 1, 'code' => 'MATH-3', 'intitule' => 'Mathématiques', 'ponderation' => 2, 'statut' => 'actif']);
    DB::table('professeurs')->insert(['id' => 1, 'matricule' => 'PROF-1', 'nom' => 'Kabila', 'postnom' => 'M', 'prenom' => 'Aline', 'sexe' => 'F', 'specialite' => 'Mathématiques', 'statut' => 'actif']);
    DB::table('professeurs')->insert(['id' => 2, 'matricule' => 'PROF-2', 'nom' => 'Ilunga', 'postnom' => 'K', 'prenom' => 'Paul', 'sexe' => 'M', 'statut' => 'actif']);
    DB::table('affectations_cours')->insert(['id' => 1, 'professeur_id' => 1, 'cours_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'statut' => 'actif']);
    DB::table('affectations_cours')->insert(['id' => 2, 'professeur_id' => 2, 'cours_id' => 1, 'classe_id' => 2, 'annee_scolaire_id' => 1, 'statut' => 'actif']);
    DB::table('emplois_du_temps')->insert(['id' => 1, 'affectation_id' => 1, 'salle_id' => 1, 'jour' => 'lundi', 'heure_debut' => '08:00', 'heure_fin' => '09:40', 'statut' => 'actif']);
    DB::table('periodes')->insert(['id' => 1, 'annee_scolaire_id' => 1, 'nom' => '1er trimestre', 'statut' => 'actif']);

    $tuteurId = DB::table('tuteurs')->insertGetId(['nom_mere' => 'Marie', 'telephone_mere' => '0990', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    foreach ([['ELV-1', 'Mwamba', 'Aline', 'F', 1], ['ELV-2', 'Kanku', 'David', 'M', 1], ['ELV-3', 'Banza', 'Sarah', 'F', 2]] as [$matricule, $nom, $prenom, $sexe, $classeId]) {
        $eleveId = DB::table('eleves')->insertGetId([
            'matricule' => $matricule, 'nom' => $nom, 'postnom' => 'T', 'prenom' => $prenom, 'sexe' => $sexe,
            'date_naissance' => '2014-05-12', 'tuteur_id' => $tuteurId, 'statut' => 'actif',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('inscriptions')->insert([
            'eleve_id' => $eleveId, 'classe_id' => $classeId, 'annee_scolaire_id' => 1,
            'date_inscription' => '2026-09-05', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
});

function professeur(int $professeurId = 1): User
{
    return User::factory()->create(['role' => 'professeur', 'professeur_id' => $professeurId]);
}

test('every teacher page answers and shows its own data', function () {
    $user = professeur();

    $this->actingAs($user)->get(route('professeur.planning'))->assertOk()->assertSee('Emploi du temps hebdomadaire')->assertSee('Mathématiques');
    $this->actingAs($user)->get(route('professeur.cours'))->assertOk()->assertSee('Mes enseignements')->assertSee('3e A');
    $this->actingAs($user)->get(route('professeur.cours.show', 1))->assertOk()->assertSee('Élèves de la classe')->assertSee('Mwamba');
    $this->actingAs($user)->get(route('professeur.evaluations'))->assertOk()->assertSee('Nouvelle évaluation');
    $this->actingAs($user)->get(route('professeur.eleves'))->assertOk()->assertSee('Mes élèves')->assertSee('Kanku');
    $this->actingAs($user)->get(route('professeur.eleves.show', 2))->assertOk()->assertSee('Fiche');
    $this->actingAs($user)->get(route('professeur.presences'))->assertOk()->assertSee('Feuille d’appel');
    $this->actingAs($user)->get(route('professeur.dashboard'))->assertOk()->assertSee('Vue générale', false)->assertSee('Accès rapides');
    // Le profil générique (/mon-profil) est suspendu dans l'interface enseignant.
    $this->actingAs($user)->get(route('professeur.dashboard'))->assertOk()->assertDontSee(route('profile.show'), false);
    // Profil enseignant (design « panel » : bandeau, barre d’action, tableau d’affectations).
    $this->actingAs($user)->get(route('professeur.profil'))
        ->assertOk()
        ->assertSee('Informations de l’enseignant')
        ->assertSee('Mes enseignements')
        ->assertSee('Mathématiques')
        ->assertSee('3e A')
        ->assertDontSee(route('profile.show'), false);
});

test('a teacher only reaches his own courses and classes', function () {
    $autre = professeur(2);

    // L’affectation 1 appartient au professeur 1, pas au professeur 2.
    $this->actingAs($autre)->get(route('professeur.cours.show', 1))->assertForbidden();
    // …mais il accède bien à son propre cours et à ses propres élèves.
    $this->actingAs($autre)->get(route('professeur.cours.show', 2))->assertOk();
    $this->actingAs($autre)->get(route('professeur.eleves.show', 1))->assertForbidden();

    // Un rôle non enseignant n’accède pas à l’espace.
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get(route('professeur.planning'))->assertForbidden();
    $this->actingAs($admin)->get(route('professeur.presences'))->assertForbidden();
});

test('a teacher creates an evaluation and records the marks', function () {
    $user = professeur();

    $this->actingAs($user)
        ->post(route('professeur.evaluations.store'), [
            'affectation_id' => 1, 'periode_id' => 1, 'titre' => 'Interro n°1',
            'type_evaluation' => 'interro', 'max_note' => 20, 'date_evaluation' => '2026-10-10',
        ])
        ->assertRedirect(route('professeur.notes', 1));

    expect(DB::table('evaluations')->count())->toBe(1);

    $this->actingAs($user)
        ->get(route('professeur.notes', 1))
        ->assertOk()
        ->assertSee('Interro n°1');

    $this->actingAs($user)
        ->post(route('professeur.notes.store', 1), [
            'notes' => [1 => 15.5, 2 => 12],
            'observations' => [1 => 'Bien', 2 => null],
        ])
        ->assertSessionHas('success', fn (string $m) => str_contains($m, '2 note(s)'));

    expect(DB::table('notes')->count())->toBe(2)
        ->and((float) DB::table('notes')->where('eleve_id', 1)->value('note_obtenue'))->toBe(15.5);

    // Une note au-delà du maximum est refusée.
    $this->actingAs($user)
        ->post(route('professeur.notes.store', 1), ['notes' => [1 => 25]])
        ->assertSessionHasErrors('notes.1');
});

test('a teacher records the roll call without duplicating and updates an existing one', function () {
    $user = professeur();

    $this->actingAs($user)
        ->post(route('professeur.presences.store'), [
            'affectation_id' => 1, 'date_seance' => '2026-10-05',
            'statuts' => [1 => 'absent', 2 => 'present'],
            'motifs' => [1 => 'Maladie', 2 => ''],
        ])
        ->assertSessionHas('success', fn (string $m) => str_contains($m, '2 élève(s)'));

    expect(DB::table('presences')->count())->toBe(2)
        ->and(DB::table('presences')->where('eleve_id', 1)->value('statut_presence'))->toBe('absent');

    // Le même appel, corrigé : mise à jour et non duplication.
    $this->actingAs($user)
        ->post(route('professeur.presences.store'), [
            'affectation_id' => 1, 'date_seance' => '2026-10-05',
            'statuts' => [1 => 'present', 2 => 'present'],
        ]);

    expect(DB::table('presences')->count())->toBe(2)
        ->and(DB::table('presences')->where('eleve_id', 1)->value('statut_presence'))->toBe('present');

    // Un autre professeur ne peut pas appeler sur une affectation qui n’est pas la sienne.
    $autre = professeur(2);
    $this->actingAs($autre)
        ->post(route('professeur.presences.store'), [
            'affectation_id' => 1, 'date_seance' => '2026-10-06', 'statuts' => [1 => 'absent'],
        ])
        ->assertSessionHasErrors('affectation_id');
});
