<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'emplois_du_temps', 'evaluations', 'affectations_cours', 'documents_professeurs',
        'professeurs', 'cours', 'inscriptions', 'eleves', 'tuteurs', 'classes',
        'cycles', 'salles', 'annees_scolaires', 'users',
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
        $table->unsignedInteger('max_travaux_journaliers')->nullable();
        $table->unsignedInteger('max_examen')->nullable();
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
    Schema::create('evaluations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
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

    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);
    DB::table('salles')->insert(['id' => 1, 'nom' => 'Salle 1', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'salle_id' => 1, 'nom' => '3e A', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 2, 'cycle_id' => 1, 'nom' => '4e B', 'statut' => 'actif']);
    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif',
    ]);
});

function seedProfesseur(array $overrides = []): int
{
    return DB::table('professeurs')->insertGetId(array_merge([
        'matricule' => 'PROF-2026-0001',
        'nom' => 'Kabila', 'postnom' => 'Mwamba', 'prenom' => 'Aline',
        'sexe' => 'F', 'specialite' => 'Mathématiques', 'telephone' => '0990000000',
        'email' => 'aline.kabila@example.test', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ], $overrides));
}

function seedCours(array $overrides = []): int
{
    return DB::table('cours')->insertGetId(array_merge([
        'code' => 'MATH6', 'intitule' => 'Mathématiques 6', 'ponderation' => 4,
        'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ], $overrides));
}

function seedAffectation(int $professeurId, int $coursId, int $classeId, int $anneeId = 1): int
{
    return DB::table('affectations_cours')->insertGetId([
        'professeur_id' => $professeurId, 'cours_id' => $coursId, 'classe_id' => $classeId,
        'annee_scolaire_id' => $anneeId, 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

test('the professor list shows the teaching staff and can be filtered', function () {
    seedProfesseur();
    seedProfesseur(['matricule' => 'PROF-2026-0002', 'nom' => 'Ilunga', 'postnom' => 'Mukendi', 'prenom' => 'David', 'specialite' => 'Physique', 'email' => 'david@example.test', 'statut' => 'inactif']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.professeurs.index'))
        ->assertOk()
        ->assertSee('Liste des professeurs')
        ->assertSee('Kabila Mwamba Aline')
        ->assertSee('Mathématiques')
        ->assertSee('PROF-2026-0001');

    $this->actingAs($admin)
        ->get(route('admin.professeurs.index', ['q' => 'Physique']))
        ->assertOk()
        ->assertSee('Ilunga Mukendi David')
        ->assertDontSee('Kabila Mwamba Aline');

    $this->actingAs($admin)
        ->get(route('admin.professeurs.index', ['statut' => 'inactif']))
        ->assertOk()
        ->assertSee('Ilunga Mukendi David')
        ->assertDontSee('Kabila Mwamba Aline');
});

test('the professor file shows linked account, affectations and toggles status', function () {
    $professeurId = seedProfesseur();
    $coursId = seedCours();
    seedAffectation($professeurId, $coursId, 1);
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'professeur', 'professeur_id' => $professeurId, 'username' => 'aline.prof']);

    $this->actingAs($admin)
        ->get(route('admin.professeurs.show', $professeurId))
        ->assertOk()
        ->assertSee('Dossier du professeur')
        ->assertSee('aline.prof')
        ->assertSee('Mathématiques 6')
        ->assertSee('3e A');

    $this->actingAs($admin)
        ->patch(route('admin.professeurs.toggle-status', $professeurId))
        ->assertRedirect();

    expect(DB::table('professeurs')->where('id', $professeurId)->value('statut'))->toBe('inactif');
});

test('an admin can create a cours and an affectation for the current year', function () {
    $professeurId = seedProfesseur();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.cours.store'), ['code' => 'FRAN6', 'intitule' => 'Français 6', 'ponderation' => 3])
        ->assertRedirect(route('admin.cours.index'));
    $this->assertDatabaseHas('cours', ['code' => 'FRAN6']);

    $coursId = (int) DB::table('cours')->where('code', 'FRAN6')->value('id');

    $this->actingAs($admin)
        ->post(route('admin.affectations.store'), [
            'professeur_id' => $professeurId, 'cours_id' => $coursId, 'classe_id' => 1,
        ])
        ->assertRedirect(route('admin.affectations.index'));

    expect(DB::table('affectations_cours')->count())->toBe(1);
    expect((int) DB::table('affectations_cours')->value('annee_scolaire_id'))
        ->toBe((int) DB::table('annees_scolaires')->where('is_encours', true)->value('id'));
});

test('a duplicate affectation is rejected and a cours in use cannot be deleted', function () {
    $professeurId = seedProfesseur();
    $coursId = seedCours();
    seedAffectation($professeurId, $coursId, 1);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.affectations.store'), [
            'professeur_id' => $professeurId, 'cours_id' => $coursId, 'classe_id' => 1,
        ])
        ->assertSessionHas('error');

    expect(DB::table('affectations_cours')->count())->toBe(1);

    $this->actingAs($admin)
        ->delete(route('admin.cours.destroy', $coursId))
        ->assertSessionHas('error');

    expect(DB::table('cours')->count())->toBe(1);
});

test('an admin can schedule a timetable slot and conflicts are rejected', function () {
    $professeurId = seedProfesseur();
    $coursId = seedCours();
    $affectationId = seedAffectation($professeurId, $coursId, 1);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.emplois-du-temps.store'), [
            'affectation_id' => $affectationId, 'jour' => 'lundi',
            'heure_debut' => '08:00', 'heure_fin' => '09:00', 'salle_id' => 1,
        ])
        ->assertRedirect(route('admin.emplois-du-temps.index'));

    expect(DB::table('emplois_du_temps')->count())->toBe(1);

    // Même classe, même jour, chevauchement -> refusé.
    $this->actingAs($admin)
        ->post(route('admin.emplois-du-temps.store'), [
            'affectation_id' => $affectationId, 'jour' => 'lundi',
            'heure_debut' => '08:30', 'heure_fin' => '09:30',
        ])
        ->assertSessionHas('error');

    expect(DB::table('emplois_du_temps')->count())->toBe(1);

    $this->actingAs($admin)
        ->get(route('admin.emplois-du-temps.index'))
        ->assertOk()
        ->assertSee('08:00')
        ->assertSee('3e A');
});

test('the timetable refuses an end time before the start time', function () {
    $professeurId = seedProfesseur();
    $affectationId = seedAffectation($professeurId, seedCours(), 1);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.emplois-du-temps.store'), [
            'affectation_id' => $affectationId, 'jour' => 'mardi',
            'heure_debut' => '10:00', 'heure_fin' => '09:00',
        ])
        ->assertSessionHasErrors('heure_fin');

    expect(DB::table('emplois_du_temps')->count())->toBe(0);
});

test('the professor menu links point to the new modules', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.professeurs.index'), false)
        ->assertSee(route('admin.affectations.index'), false)
        ->assertSee(route('admin.emplois-du-temps.index'), false);
});

test('the professor detail page offers a timetable button that renders the weekly grid', function () {
    $professeurId = seedProfesseur();
    $affectationId = seedAffectation($professeurId, seedCours(), 1);
    DB::table('emplois_du_temps')->insert([
        'affectation_id' => $affectationId, 'salle_id' => 1, 'jour' => 'lundi',
        'heure_debut' => '08:00', 'heure_fin' => '09:30', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.professeurs.show', $professeurId))
        ->assertOk()
        ->assertSee(route('admin.professeurs.emploi-du-temps', $professeurId), false);

    $this->actingAs($admin)
        ->get(route('admin.professeurs.emploi-du-temps', $professeurId))
        ->assertOk()
        ->assertSee('Emploi du temps hebdomadaire')
        ->assertSee('Mathématiques 6')
        ->assertSee('3e A')
        ->assertSee('Salle 1')
        ->assertSee('08:00');
});

test('an admin can edit a professor profile from the file and attach documents', function () {
    Storage::fake('public');
    $professeurId = seedProfesseur();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.professeurs.show', $professeurId))
        ->assertOk()
        ->assertSee(route('admin.professeurs.edit', $professeurId), false)
        ->assertSee('Ajouter un document');

    $this->actingAs($admin)
        ->get(route('admin.professeurs.edit', $professeurId))
        ->assertOk()
        ->assertSee('Modifier le professeur')
        ->assertSee('Kabila');

    $this->actingAs($admin)
        ->put(route('admin.professeurs.update', $professeurId), [
            'nom' => 'Kabila', 'postnom' => 'Mwamba', 'prenom' => 'Aline',
            'sexe' => 'F', 'specialite' => 'Informatique', 'telephone' => '0810000000',
            'email' => 'aline.kabila@example.test', 'statut' => 'actif',
        ])
        ->assertRedirect(route('admin.professeurs.show', $professeurId));

    expect(DB::table('professeurs')->where('id', $professeurId)->value('specialite'))->toBe('Informatique');

    $this->actingAs($admin)
        ->post(route('admin.professeurs.documents.store', $professeurId), [
            'titre' => 'Diplôme d’État',
            'type_document' => 'diplome',
            'document' => UploadedFile::fake()->create('diplome.pdf', 120, 'application/pdf'),
        ])
        ->assertRedirect();

    $document = DB::table('documents_professeurs')->first();
    expect($document)->not->toBeNull()
        ->and($document->titre)->toBe('Diplôme d’État')
        ->and($document->type_document)->toBe('diplome')
        ->and($document->chemin_fichier)->not->toBeNull();

    $this->actingAs($admin)
        ->delete(route('admin.professeurs.documents.destroy', $document->id))
        ->assertRedirect();

    expect(DB::table('documents_professeurs')->count())->toBe(0);
});

test('a professor email stays unique when updated', function () {
    seedProfesseur();
    $otherId = seedProfesseur(['matricule' => 'PROF-2026-0002', 'email' => 'autre.prof@example.test']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->put(route('admin.professeurs.update', $otherId), [
            'nom' => 'Kabila', 'postnom' => 'Mwamba', 'prenom' => 'Aline',
            'sexe' => 'F', 'specialite' => 'Maths', 'telephone' => '0990',
            'email' => 'aline.kabila@example.test', 'statut' => 'actif',
        ])
        ->assertSessionHasErrors('email');
});

test('la page des cours gère la création, la modification et le statut', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $coursId = seedCours();

    $this->actingAs($admin)->get(route('admin.cours.index'))
        ->assertOk()
        ->assertSee('Cours (branches)');

    $this->actingAs($admin)
        ->put(route('admin.cours.update', $coursId), ['code' => 'MATH6', 'intitule' => 'Maths 6', 'ponderation' => 5])
        ->assertRedirect(route('admin.cours.index'));

    expect((int) DB::table('cours')->where('id', $coursId)->value('ponderation'))->toBe(5);

    $this->actingAs($admin)->patch(route('admin.cours.toggle-status', $coursId))->assertRedirect();

    expect(DB::table('cours')->where('id', $coursId)->value('statut'))->toBe('inactif');
});

test('une affectation peut être mutée vers un autre professeur', function () {
    $p1 = seedProfesseur();
    $p2 = seedProfesseur(['matricule' => 'PROF-2026-0002', 'email' => 'p2@example.test']);
    $coursId = seedCours();
    $affectationId = seedAffectation($p1, $coursId, 1);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patch(route('admin.affectations.muter', $affectationId), [
            'professeur_id' => $p2, 'cours_id' => $coursId, 'classe_id' => 1,
        ])
        ->assertRedirect();

    expect((int) DB::table('affectations_cours')->where('id', $affectationId)->value('professeur_id'))->toBe($p2);
});

test('deux affectations peuvent permuter leurs professeurs', function () {
    $p1 = seedProfesseur();
    $p2 = seedProfesseur(['matricule' => 'PROF-2026-0002', 'email' => 'p2@example.test']);
    $maths = seedCours();
    $francais = seedCours(['code' => 'FRAN6', 'intitule' => 'Français 6']);
    $a = seedAffectation($p1, $maths, 1);
    $b = seedAffectation($p2, $francais, 1);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.affectations.permuter'), ['affectation_a' => $a, 'affectation_b' => $b])
        ->assertRedirect();

    expect((int) DB::table('affectations_cours')->where('id', $a)->value('professeur_id'))->toBe($p2)
        ->and((int) DB::table('affectations_cours')->where('id', $b)->value('professeur_id'))->toBe($p1);
});

test('la carte professionnelle du professeur affiche le QR unique et le matricule', function () {
    $professeurId = seedProfesseur();
    $admin = User::factory()->create(['role' => 'admin']);
    $professeur = \App\Models\Professeurs::findOrFail($professeurId);

    $this->actingAs($admin)
        ->get(route('admin.professeurs.carte', $professeur))
        ->assertOk()
        ->assertSee('Carte professionnelle', false)
        ->assertSee('id="carte-qr"', false)
        ->assertSee('js/qrcode.min.js', false)
        ->assertSee('PROF-2026-0001');
});
