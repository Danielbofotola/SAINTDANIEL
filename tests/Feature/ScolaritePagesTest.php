<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'bulletins', 'notes', 'evaluations', 'periodes', 'emplois_du_temps', 'affectations_cours',
        'documents_professeurs', 'professeurs', 'cours', 'inscriptions', 'eleves', 'tuteurs',
        'classes', 'cycles', 'salles', 'annees_scolaires', 'users',
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
        $table->string('matricule')->unique();
        $table->string('nom', 50);
        $table->string('postnom', 50);
        $table->string('prenom', 50);
        $table->enum('sexe', ['M', 'F']);
        $table->string('specialite', 100);
        $table->string('telephone', 20);
        $table->string('email', 100)->unique();
        $table->string('photo')->nullable();
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });
    Schema::create('documents_professeurs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id');
        $table->string('titre', 150);
        $table->string('type_document');
        $table->string('chemin_fichier');
        $table->unsignedInteger('taille_ko')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('cours', function (Blueprint $table) {
        $table->id();
        $table->string('code', 20)->unique();
        $table->string('intitule', 100);
        $table->unsignedInteger('ponderation')->default(1);
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
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
    Schema::create('tuteurs', function (Blueprint $table) {
        $table->id();
        $table->string('nom_mere');
        $table->string('telephone_mere');
        $table->string('email_mere')->nullable();
        $table->text('adresse_mere')->nullable();
        $table->string('nom_pere')->nullable();
        $table->string('telephone_pere')->nullable();
        $table->string('email_pere')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->unsignedBigInteger('section_id')->nullable();
        $table->unsignedBigInteger('option_id')->nullable();
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
        $table->date('date_naissance');
        $table->string('lieu_naissance')->nullable();
        $table->unsignedBigInteger('tuteur_id');
        $table->string('photo')->nullable();
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
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });
    Schema::create('emplois_du_temps', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->enum('jour', ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi']);
        $table->time('heure_debut');
        $table->time('heure_fin');
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });
    Schema::create('periodes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('nom', 50);
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });
    Schema::create('evaluations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
        $table->unsignedBigInteger('periode_id');
        $table->string('titre', 100);
        $table->enum('type_evaluation', ['interro', 'devoir', 'examen', 'autre'])->default('interro');
        $table->decimal('max_note', 5, 2)->default(20);
        $table->date('date_evaluation');
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });
    Schema::create('notes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('evaluation_id');
        $table->unsignedBigInteger('eleve_id');
        $table->decimal('note_obtenue', 5, 2);
        $table->string('observation')->nullable();
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
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
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });

    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);
    DB::table('salles')->insert(['id' => 1, 'nom' => 'Salle 1', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'salle_id' => 1, 'nom' => '3e A', 'statut' => 'actif']);
    DB::table('annees_scolaires')->insert([
        ['id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif'],
        ['id' => 2, 'libelle' => '2025-2026', 'date_debut' => '2025-09-01', 'date_fin' => '2026-07-01', 'is_encours' => false, 'statut' => 'actif'],
    ]);
    DB::table('professeurs')->insert([
        'id' => 1, 'matricule' => 'PROF-2026-0001', 'nom' => 'Kabila', 'postnom' => 'Mwamba', 'prenom' => 'Aline',
        'sexe' => 'F', 'specialite' => 'Mathématiques', 'telephone' => '0990000000',
        'email' => 'aline.kabila@example.test', 'statut' => 'actif',
    ]);
    DB::table('cours')->insert(['id' => 1, 'code' => 'MATH6', 'intitule' => 'Mathématiques 6', 'ponderation' => 4, 'statut' => 'actif']);
    DB::table('affectations_cours')->insert([
        'id' => 1, 'professeur_id' => 1, 'cours_id' => 1, 'classe_id' => 1,
        'annee_scolaire_id' => 1, 'statut' => 'actif',
    ]);
    DB::table('periodes')->insert(['id' => 1, 'annee_scolaire_id' => 1, 'nom' => '1ère Période', 'statut' => 'actif']);
});

function seedEleveInscrit(string $nom, string $matricule, string $sexe = 'M'): int
{
    $tuteurId = DB::table('tuteurs')->insertGetId([
        'nom_mere' => 'Marie '.$nom, 'telephone_mere' => '0990000000', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $eleveId = DB::table('eleves')->insertGetId([
        'matricule' => $matricule, 'nom' => $nom, 'postnom' => 'Test', 'prenom' => 'Eleve',
        'sexe' => $sexe, 'date_naissance' => '2014-05-12', 'tuteur_id' => $tuteurId,
        'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);

    DB::table('inscriptions')->insert([
        'eleve_id' => $eleveId, 'classe_id' => 1, 'annee_scolaire_id' => 1,
        'date_inscription' => '2026-09-05', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return $eleveId;
}

function seedEvaluation(int $periodeId = 1, float $maxNote = 20): int
{
    return DB::table('evaluations')->insertGetId([
        'affectation_id' => 1, 'periode_id' => $periodeId, 'titre' => 'Interro n°1',
        'type_evaluation' => 'interro', 'max_note' => $maxNote, 'date_evaluation' => '2026-10-01',
        'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

test('an admin can create a period and duplicate names are rejected', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.periodes.store'), ['nom' => '2ème Période'])
        ->assertRedirect(route('admin.periodes.index'));

    $this->assertDatabaseHas('periodes', ['nom' => '2ème Période', 'annee_scolaire_id' => 1]);

    $this->actingAs($admin)
        ->post(route('admin.periodes.store'), ['nom' => '2ème Période'])
        ->assertSessionHasErrors('nom');

    expect(DB::table('periodes')->count())->toBe(2);
});

test('a period with evaluations cannot be deleted and toggles status', function () {
    seedEvaluation();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->delete(route('admin.periodes.destroy', 1))
        ->assertSessionHas('error');
    expect(DB::table('periodes')->count())->toBe(1);

    $this->actingAs($admin)
        ->patch(route('admin.periodes.toggle-status', 1))
        ->assertRedirect();
    expect(DB::table('periodes')->where('id', 1)->value('statut'))->toBe('inactif');
});

test('an admin can create an evaluation only for the current year', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.evaluations.store'), [
            'affectation_id' => 1, 'periode_id' => 1, 'titre' => 'Devoir 1',
            'type_evaluation' => 'devoir', 'max_note' => 20, 'date_evaluation' => '2026-10-05',
        ])
        ->assertRedirect(route('admin.evaluations.index'));

    $this->assertDatabaseHas('evaluations', ['titre' => 'Devoir 1', 'periode_id' => 1]);

    // Période d'une autre année scolaire -> refusée.
    DB::table('periodes')->insert(['id' => 9, 'annee_scolaire_id' => 2, 'nom' => 'Ancienne période', 'statut' => 'actif']);

    $this->actingAs($admin)
        ->post(route('admin.evaluations.store'), [
            'affectation_id' => 1, 'periode_id' => 9, 'titre' => 'Devoir 2',
            'type_evaluation' => 'devoir', 'max_note' => 20, 'date_evaluation' => '2026-10-06',
        ])
        ->assertSessionHasErrors('periode_id');
});

test('notes can be captured and are bounded by the maximum note', function () {
    $eleveA = seedEleveInscrit('Mwamba', 'ELV-2026-0001');
    $eleveB = seedEleveInscrit('Ilunga', 'ELV-2026-0002', 'F');
    $evaluationId = seedEvaluation();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.evaluations.notes', $evaluationId))
        ->assertOk()
        ->assertSee('Mwamba');

    $this->actingAs($admin)
        ->post(route('admin.evaluations.notes.store', $evaluationId), [
            'notes' => [$eleveA => 16, $eleveB => 10],
        ])
        ->assertRedirect(route('admin.evaluations.notes', $evaluationId));

    expect(DB::table('notes')->where('evaluation_id', $evaluationId)->count())->toBe(2);
    expect((float) DB::table('notes')->where('eleve_id', $eleveA)->value('note_obtenue'))->toBe(16.0);

    // Note supérieure au maximum -> refusée.
    $this->actingAs($admin)
        ->post(route('admin.evaluations.notes.store', $evaluationId), ['notes' => [$eleveA => 25]])
        ->assertSessionHasErrors('notes.'.$eleveA);

    expect((float) DB::table('notes')->where('eleve_id', $eleveA)->value('note_obtenue'))->toBe(16.0);
});

test('bulletins are computed with rank and mention from the captured notes', function () {
    $eleveA = seedEleveInscrit('Mwamba', 'ELV-2026-0001');
    $eleveB = seedEleveInscrit('Ilunga', 'ELV-2026-0002', 'F');
    $evaluationId = seedEvaluation();
    $admin = User::factory()->create(['role' => 'admin']);

    DB::table('notes')->insert([
        ['evaluation_id' => $evaluationId, 'eleve_id' => $eleveA, 'note_obtenue' => 16, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()],
        ['evaluation_id' => $evaluationId, 'eleve_id' => $eleveB, 'note_obtenue' => 10, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->actingAs($admin)
        ->get(route('admin.bulletins.index', ['periode_id' => 1, 'classe_id' => 1]))
        ->assertOk()
        ->assertSee('Mwamba')
        ->assertSee('80.00 %')
        ->assertSee('Distinction')
        ->assertSee('50.00 %');

    $this->actingAs($admin)
        ->post(route('admin.bulletins.generate'), ['periode_id' => 1, 'classe_id' => 1])
        ->assertRedirect(route('admin.bulletins.index', ['periode_id' => 1, 'classe_id' => 1]));

    $bulletinA = DB::table('bulletins')->where('periode_id', 1)
        ->where('inscription_id', DB::table('inscriptions')->where('eleve_id', $eleveA)->value('id'))
        ->first();

    expect($bulletinA)->not->toBeNull()
        ->and((float) $bulletinA->total_points)->toBe(16.0)
        ->and((float) $bulletinA->max_possible)->toBe(20.0)
        ->and((float) $bulletinA->pourcentage)->toBe(80.0)
        ->and((int) $bulletinA->place_classe)->toBe(1)
        ->and($bulletinA->mention)->toBe('Distinction');

    $bulletinB = DB::table('bulletins')->where('periode_id', 1)
        ->where('inscription_id', DB::table('inscriptions')->where('eleve_id', $eleveB)->value('id'))
        ->first();
    expect((int) $bulletinB->place_classe)->toBe(2)
        ->and($bulletinB->mention)->toBe('Faible');
});

test('the school menu links point to periods, evaluations and bulletins', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.periodes.index'), false)
        ->assertSee(route('admin.evaluations.index'), false)
        ->assertSee(route('admin.bulletins.index'), false);
});
