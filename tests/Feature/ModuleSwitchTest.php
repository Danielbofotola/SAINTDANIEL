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
        $table->string('nom_etablissement', 150)->default('COMPLEXE SCOLAIRE BABAZIN');
        $table->string('nom_court', 60)->default('C.S. Moty');
        $table->string('telephone', 30)->nullable();
        $table->string('email', 150)->nullable();
        $table->text('adresse')->nullable();
        $table->boolean('saisie_notes_active')->default(true);
        $table->boolean('portail_parents_actif')->default(false);
        $table->boolean('deliberation_active')->default(false);
        $table->decimal('deliberation_seuil_admission', 5, 2)->default(50);
        $table->decimal('deliberation_seuil_repechage', 5, 2)->default(45);
        $table->unsignedInteger('deliberation_max_cours_echoues')->nullable();
        $table->decimal('deliberation_note_min_cours', 5, 2)->nullable();
        // Réglages du bulletin (migration 2026_10_23_100000).
        $table->string('bulletin_pays', 100)->nullable();
        $table->string('bulletin_ministere', 160)->nullable();
        $table->string('bulletin_titre', 160)->nullable();
        $table->string('bulletin_reference', 50)->nullable();
        $table->string('bulletin_province', 100)->nullable();
        $table->string('bulletin_ville', 100)->nullable();
        $table->string('bulletin_commune', 100)->nullable();
        $table->string('bulletin_code_ecole', 50)->nullable();
        $table->string('bulletin_lieu_signature', 100)->nullable();
        $table->string('bulletin_signataire', 120)->nullable();
        $table->decimal('bulletin_seuil_reussite', 5, 2)->default(50);
        $table->boolean('bulletin_afficher_place')->default(true);
        $table->boolean('bulletin_afficher_conduite')->default(true);
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
    DB::table('cours')->insert(['id' => 1, 'code' => 'MATH6', 'intitule' => 'Maths 6', 'ponderation' => 4, 'statut' => 'actif']);
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

test('the module switch page renders and persists the flags', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.modules.index'))
        ->assertOk()
        ->assertSee('Commuter les modules')
        ->assertSee('Saisie des notes')
        ->assertSee('Portail Parents');

    // Saisie des notes décochée, portail coché.
    $this->actingAs($admin)
        ->put(route('admin.modules.update'), ['portail_parents_actif' => '1'])
        ->assertRedirect(route('admin.modules.index'));

    $settings = DB::table('parametres_systeme')->first();
    expect((int) $settings->saisie_notes_active)->toBe(0)
        ->and((int) $settings->portail_parents_actif)->toBe(1);

    // On réactive la saisie des notes.
    $this->actingAs($admin)
        ->put(route('admin.modules.update'), ['saisie_notes_active' => '1'])
        ->assertRedirect(route('admin.modules.index'));

    expect((int) DB::table('parametres_systeme')->value('saisie_notes_active'))->toBe(1);
});

test('disabling the notes module blocks the notes entry and the capture', function () {
    DB::table('parametres_systeme')->insert([
        'nom_etablissement' => 'École', 'nom_court' => 'É', 'saisie_notes_active' => false,
        'portail_parents_actif' => false, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $admin = User::factory()->create(['role' => 'admin']);

    // La page de saisie refuse l'accès.
    $this->actingAs($admin)
        ->get(route('admin.evaluations.notes', 1))
        ->assertRedirect(route('admin.evaluations.index'))
        ->assertSessionHas('error');

    // L'enregistrement des notes est refusé.
    $this->actingAs($admin)
        ->post(route('admin.evaluations.notes.store', 1), ['notes' => [$this->eleveId => 15]])
        ->assertSessionHas('error');

    expect(DB::table('notes')->count())->toBe(0);

    // La liste des évaluations affiche l'avertissement.
    $this->actingAs($admin)
        ->get(route('admin.evaluations.index'))
        ->assertOk()
        ->assertSee('désactivée');
});

test('the admin profile sidebar links to the module switch page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.profile.show'))
        ->assertOk()
        ->assertSee(route('admin.modules.index'), false)
        // Les options métier sont accessibles depuis le profil administrateur.
        ->assertSee(route('admin.deliberations.index'), false)
        ->assertSee(route('admin.messagerie.index'), false)
        ->assertSee('Délibération & passage de classe')
        ->assertSee('Messagerie (modération)')
        ->assertSee('Portail Parents, Délibération');
});
