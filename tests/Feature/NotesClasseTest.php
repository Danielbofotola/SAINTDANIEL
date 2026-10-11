<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'notes', 'evaluations', 'periodes', 'affectations_cours', 'cours', 'professeurs',
        'inscriptions', 'eleves', 'tuteurs', 'classes', 'cycles', 'salles',
        'annees_scolaires', 'parametres_systeme', 'users',
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
    Schema::create('parametres_systeme', function (Blueprint $table) {
        $table->id();
        $table->string('nom_etablissement', 150)->default('École');
        $table->string('nom_court', 60)->default('É');
        $table->string('telephone', 30)->nullable();
        $table->string('email', 150)->nullable();
        $table->text('adresse')->nullable();
        $table->boolean('saisie_notes_active')->default(true);
        $table->boolean('portail_parents_actif')->default(false);
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
    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('matricule')->unique();
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->enum('sexe', ['M', 'F']);
        $table->string('specialite');
        $table->string('telephone');
        $table->string('email');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('cours', function (Blueprint $table) {
        $table->id();
        $table->string('code', 20)->unique();
        $table->string('intitule', 100);
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
    Schema::create('periodes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('nom', 50);
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('evaluations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
        $table->unsignedBigInteger('periode_id');
        $table->string('titre', 100);
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

    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif',
    ]);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'nom' => '3e A', 'statut' => 'actif']);
    DB::table('professeurs')->insert([
        'id' => 1, 'matricule' => 'PROF-2026-0001', 'nom' => 'Kabila', 'postnom' => 'M', 'prenom' => 'Aline',
        'sexe' => 'F', 'specialite' => 'Maths', 'telephone' => '0990', 'email' => 'a@example.test', 'statut' => 'actif',
    ]);
    DB::table('cours')->insert(['id' => 1, 'code' => 'MATH6', 'intitule' => 'Mathématiques 6', 'ponderation' => 4, 'statut' => 'actif']);
    DB::table('affectations_cours')->insert([
        'id' => 1, 'professeur_id' => 1, 'cours_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'statut' => 'actif',
    ]);
    DB::table('periodes')->insert(['id' => 1, 'annee_scolaire_id' => 1, 'nom' => '1ère Période', 'statut' => 'actif']);
    DB::table('evaluations')->insert([
        'id' => 1, 'affectation_id' => 1, 'periode_id' => 1, 'titre' => 'Interro n°1', 'type_evaluation' => 'interro',
        'max_note' => 20, 'date_evaluation' => '2026-10-01', 'statut' => 'actif',
    ]);
    $tuteurId = DB::table('tuteurs')->insertGetId(['nom_mere' => 'Marie', 'telephone_mere' => '0990', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $eleveId = DB::table('eleves')->insertGetId([
        'matricule' => 'ELV-2026-0001', 'nom' => 'Mwamba', 'postnom' => 'T', 'prenom' => 'Aline', 'sexe' => 'F',
        'date_naissance' => '2014-05-12', 'tuteur_id' => $tuteurId, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('inscriptions')->insert([
        'eleve_id' => $eleveId, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-05',
        'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $this->eleveId = $eleveId;
});

test('the notes page filters the class and saves the entered marks', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.notes.index', ['classe_id' => 1]))
        ->assertOk()
        ->assertSee('Saisie des notes')
        ->assertSee('Mwamba')
        ->assertSee('Mathématiques 6');

    $this->actingAs($admin)
        ->post(route('admin.notes.store'), [
            'classe_id' => 1,
            'periode_id' => 1,
            'notes' => [$this->eleveId => [1 => 15]],
        ])
        ->assertRedirect(route('admin.notes.index', ['classe_id' => 1, 'periode_id' => 1]));

    expect(DB::table('notes')->where('evaluation_id', 1)->where('eleve_id', $this->eleveId)->count())->toBe(1)
        ->and((float) DB::table('notes')->where('evaluation_id', 1)->value('note_obtenue'))->toBe(15.0);
});

test('a mark above the evaluation maximum is ignored', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.notes.store'), [
            'classe_id' => 1,
            'notes' => [$this->eleveId => [1 => 25]],
        ])
        ->assertRedirect();

    expect(DB::table('notes')->count())->toBe(0);
});

test('the notes capture is blocked when the notes module is disabled', function () {
    DB::table('parametres_systeme')->insert([
        'nom_etablissement' => 'École', 'nom_court' => 'É', 'saisie_notes_active' => false,
        'portail_parents_actif' => false, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.notes.store'), ['classe_id' => 1, 'notes' => [$this->eleveId => [1 => 10]]])
        ->assertSessionHas('error');

    expect(DB::table('notes')->count())->toBe(0);
});
