<?php

use App\Models\Classe;
use App\Models\Cycle;
use App\Models\Eleve;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('users');
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->foreignId('professeur_id')->nullable();
        $table->string('nom', 50)->nullable();
        $table->string('postnom', 50)->nullable();
        $table->string('prenom', 50)->nullable();
        $table->date('date_naissance')->nullable();
        $table->string('username', 50)->unique();
        $table->string('email', 100)->unique();
        $table->string('telephone', 20)->nullable();
        $table->text('adresse')->nullable();
        $table->string('photo')->nullable();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->dateTime('last_login')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });
});

test('admin can temporarily access an active user account and return to their own session', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($admin)
        ->get(route('admin.profile.accounts'))
        ->assertOk()
        ->assertSee('Accéder au compte');

    $this->post(route('admin.profile.accounts.impersonate', $user))
        ->assertRedirect(route('comptable.dashboard'));

    $this->assertAuthenticatedAs($user);
    $this->assertSame($admin->id, session('impersonating_admin_id'));
    $this->get(route('comptable.dashboard'))->assertSee('Revenir à mon compte administrateur');

    $this->post(route('admin.impersonation.stop'))
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
    $this->assertNull(session('impersonating_admin_id'));
});

test('admin cannot impersonate another admin or an inactive account', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $otherAdmin = User::factory()->create(['role' => 'admin']);
    $inactiveUser = User::factory()->create(['role' => 'comptable', 'statut' => 'inactif']);

    $this->actingAs($admin)
        ->post(route('admin.profile.accounts.impersonate', $otherAdmin))
        ->assertSessionHas('error');

    $this->post(route('admin.profile.accounts.impersonate', $inactiveUser))
        ->assertSessionHas('error');

    $this->assertAuthenticatedAs($admin);
});

test('an impersonated session cannot access admin routes if the user role changes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($admin)
        ->post(route('admin.profile.accounts.impersonate', $user))
        ->assertRedirect(route('comptable.dashboard'));

    $user->update(['role' => 'admin']);

    $this->get(route('admin.dashboard'))->assertForbidden();
    $this->post(route('admin.impersonation.stop'))->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($admin);
});

test('admin can edit user profile without changing password or pin', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'comptable']);
    $password = $user->password;
    $pinCode = $user->pin_code;

    $this->actingAs($admin)
        ->put(route('admin.profile.accounts.update', $user), [
            'nom' => 'Kabila',
            'username' => $user->username,
            'email' => $user->email,
            'role' => 'comptable',
            'statut' => 'actif',
        ])
        ->assertRedirect(route('admin.profile.accounts'));

    $user->refresh();
    expect($user->nom)->toBe('Kabila')
        ->and($user->password)->toBe($password)
        ->and($user->pin_code)->toBe($pinCode);
});

test('un utilisateur non administrateur consulte son profil mais ne peut plus le modifier', function () {
    $user = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($user)
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee($user->email)
        ->assertSee('Téléphone');

    // La mise à jour de ses informations et coordonnées est réservée à l'admin.
    $this->get(route('profile.edit'))->assertForbidden();

    $this->put(route('profile.update'), [
        'nom' => 'Kabila',
        'username' => $user->username,
        'email' => $user->email,
        'telephone' => '+243 900 000 000',
        'adresse' => 'Kinshasa',
    ])->assertForbidden();

    $user->refresh();
    expect($user->nom)->not->toBe('Kabila')
        ->and($user->telephone)->toBeNull()
        ->and($user->role)->toBe('comptable');
});

test('admin can edit a user while keeping their linked inactive professor selectable', function () {
    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('matricule');
        $table->string('nom');
        $table->string('postnom');
        $table->string('prenom');
        $table->string('statut');
    });
    DB::table('professeurs')->insert([
        'id' => 1,
        'matricule' => 'PROF-2026-0001',
        'nom' => 'Kabila',
        'postnom' => 'Mwamba',
        'prenom' => 'Aline',
        'statut' => 'inactif',
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'professeur', 'professeur_id' => 1]);

    $this->actingAs($admin)
        ->get(route('admin.profile.accounts.edit', $user))
        ->assertOk()
        ->assertSee('PROF-2026-0001');
});

    test('a professor without an assigned teacher record can open the professor dashboard', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $professor = User::factory()->create(['role' => 'professeur', 'professeur_id' => null]);

        $this->actingAs($admin)
            ->post(route('admin.profile.accounts.impersonate', $professor))
            ->assertRedirect(route('professeur.dashboard'));

        $this->get(route('professeur.dashboard'))
        ->assertOk()
        ->assertSee('Espace enseignant')
            ->assertSee('Revenir à mon compte administrateur')
        ->assertSee('Aucune fiche enseignant n’est reliée à ce compte.');

        $this->post(route('admin.impersonation.stop'))->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    });

test('a professor dashboard shows the teacher assigned courses and evaluations', function () {
    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('matricule');
        $table->string('nom');
        $table->string('postnom');
        $table->string('prenom');
        $table->string('sexe');
        $table->string('specialite');
        $table->string('telephone');
        $table->string('email');
        $table->string('photo')->nullable();
        $table->string('statut');
    });
    Schema::create('annees_scolaires', function (Blueprint $table) {
        $table->id();
        $table->string('libelle');
        $table->boolean('is_encours');
        $table->string('statut');
    });
    Schema::create('cours', function (Blueprint $table) {
        $table->id();
        $table->string('code');
        $table->string('intitule');
    });
    Schema::create('cycles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
    });
    Schema::create('sections', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
    });
    Schema::create('options', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
    });
    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('section_id')->nullable();
        $table->unsignedBigInteger('option_id')->nullable();
        $table->string('nom');
    });
    Schema::create('affectations_cours', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id');
        $table->unsignedBigInteger('cours_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('statut');
    });
    Schema::create('inscriptions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('statut');
    });
    Schema::create('salles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
    });
    Schema::create('emplois_du_temps', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->string('jour');
        $table->time('heure_debut');
        $table->time('heure_fin');
        $table->string('statut');
    });
    Schema::create('evaluations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
        $table->string('titre');
        $table->string('type_evaluation');
        $table->date('date_evaluation');
        $table->string('statut');
    });

    DB::table('professeurs')->insert([
        'id' => 1,
        'matricule' => 'PROF-2026-0001',
        'nom' => 'Kabila',
        'postnom' => 'Mwamba',
        'prenom' => 'Aline',
        'sexe' => 'F',
        'specialite' => 'Mathématiques',
        'telephone' => '000000000',
        'email' => 'aline@example.test',
        'photo' => 'professeurs/ancienne-photo.jpg',
        'statut' => 'actif',
    ]);
    DB::table('annees_scolaires')->insert(['id' => 1, 'libelle' => '2026-2027', 'is_encours' => true, 'statut' => 'actif']);
    DB::table('cours')->insert(['id' => 1, 'code' => 'MATH-01', 'intitule' => 'Mathématiques']);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire']);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'nom' => '3e A']);
    DB::table('affectations_cours')->insert(['id' => 1, 'professeur_id' => 1, 'cours_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'statut' => 'actif']);
    DB::table('inscriptions')->insert([
        ['eleve_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'statut' => 'actif'],
        ['eleve_id' => 2, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'statut' => 'actif'],
    ]);
    DB::table('emplois_du_temps')->insert([
        'affectation_id' => 1,
        // dayOfWeekIso va de 1 à 7 : le dimanche (7) retomberait hors du tableau (lundi→samedi).
        'jour' => ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'][min(now()->addDay()->dayOfWeekIso, 6) - 1],
        'heure_debut' => '10:00:00',
        'heure_fin' => '11:00:00',
        'statut' => 'actif',
    ]);
    DB::table('evaluations')->insert([
        'affectation_id' => 1,
        'titre' => 'Contrôle de géométrie',
        'type_evaluation' => 'interro',
        'date_evaluation' => now()->addDays(2)->toDateString(),
        'statut' => 'actif',
    ]);

    $professor = User::factory()->create([
        'role' => 'professeur',
        'professeur_id' => 1,
        'photo' => 'users/photo-mise-a-jour.jpg',
    ]);

    $this->actingAs($professor)
        ->get(route('professeur.dashboard'))
        ->assertOk()
        ->assertSee('Mathématiques')
        ->assertSee('Contrôle de géométrie')
        ->assertSee('Élèves concernés')
        ->assertSee('storage/users/photo-mise-a-jour.jpg', false)
        ->assertDontSee('storage/professeurs/ancienne-photo.jpg', false)
        ->assertSee('2');
});

test('admin can view account and school charts on a dedicated statistics page', function () {
    foreach (['eleves', 'classes', 'professeurs', 'salles'] as $tableName) {
        Schema::dropIfExists($tableName);
    }

    Schema::create('eleves', function (Blueprint $table) {
        $table->id();
        $table->string('statut');
    });
    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->string('statut');
    });
    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('statut');
    });
    Schema::create('salles', function (Blueprint $table) {
        $table->id();
        $table->string('statut');
    });

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.profile.statistics'))
        ->assertOk()
        ->assertSee('Comptes par rôle')
        ->assertSee('Ressources scolaires')
        ->assertSee('administrationChart')
        ->assertSee('resourcesChart');

    $this->get(route('admin.profile.show'))
        ->assertOk()
        ->assertSee('Sécurité du compte')
        ->assertDontSee('administrationChart')
        ->assertDontSee('resourcesChart');
});

test('admin can create a section and option before returning to the class form', function () {
    Schema::dropIfExists('options');
    Schema::dropIfExists('sections');

    // Schéma réel de `sections` : la migration align_sections_options_schema a
    // retiré les colonnes code/description/statut.
    Schema::create('sections', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 100)->unique();
        $table->timestamps();
    });
    Schema::create('options', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('section_id');
        $table->string('nom', 100);
        $table->timestamps();
        $table->unique(['section_id', 'nom']);
    });

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.sections.create'))
        ->assertOk()
        ->assertSee('Créer une section');

    $this->post(route('admin.sections.store'), [
        'nom' => 'Scientifique',
        'description' => 'Section scientifique',
    ])->assertRedirect(route('admin.classes.create', ['section_id' => 1]));

    $this->get(route('admin.options.create', ['section_id' => 1]))
        ->assertOk()
        ->assertSee('Créer une option')
        ->assertSee('Scientifique');

    $this->post(route('admin.options.store'), [
        'section_id' => 1,
        'nom' => 'Mathématiques - Physique',
    ])->assertRedirect(route('admin.classes.create', ['section_id' => 1, 'option_id' => 1]));

    // Les colonnes `code` n'existent plus (retirées par align_sections_options_schema).
    expect(DB::table('sections')->where('id', 1)->value('nom'))->toBe('Scientifique')
        ->and(DB::table('options')->where('id', 1)->value('nom'))->toBe('Mathématiques - Physique');

    foreach (['classes', 'salles', 'cycles'] as $tableName) {
        Schema::dropIfExists($tableName);
    }
    Schema::create('cycles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('statut');
    });
    Schema::create('salles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->unsignedInteger('capacite')->nullable();
        $table->string('statut');
    });
    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('cycle_id');
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->unsignedBigInteger('section_id')->nullable();
        $table->unsignedBigInteger('option_id')->nullable();
        $table->string('nom');
        $table->string('statut');
    });
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);

    $this->get(route('admin.classes.create', ['section_id' => 1, 'option_id' => 1]))
        ->assertOk()
        ->assertSee('Nouvelle section')
        ->assertSee('Nouvelle option')
        ->assertSee('data-section-id="1"', false)
        ->assertSee('selected>Mathématiques - Physique</option>', false);

    $this->withSession(['_old_input' => []])
        ->get(route('admin.classes.create'))
        ->assertOk()
        ->assertSee('data-section-id="1"', false)
        ->assertSee('hidden disabled', false);
});

test('admin dashboard detail action supplies the student data to its modal', function () {
    $cycle = new Cycle(['nom' => 'Secondaire']);
    $classe = new Classe(['nom' => '3e A']);
    $classe->setRelation('cycle', $cycle);

    $inscription = new Inscription(['statut' => 'actif']);
    $inscription->setRelation('classe', $classe);

    $eleve = new Eleve([
        'nom' => 'Kabila',
        'postnom' => 'Bofotola',
        'prenom' => 'Aline',
        'matricule' => 'EL-2026-0001',
        'sexe' => 'F',
        'date_naissance' => now()->subYears(12),
        'photo' => 'eleves/aline.jpg',
    ]);
    $eleve->setAttribute('id', 42);
    $eleve->setRelation('inscriptions', collect([$inscription]));

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->view('admin.dashboard', [
            'eleves' => collect([$eleve]),
            'statistiques' => ['garcons' => 0, 'filles' => 1, 'classes' => 1, 'professeurs' => 0],
            'occupationSalles' => collect(),
            'repartitionCycles' => collect(),
        ])
        ->assertSee('studentDetailsModal')
        ->assertSee('data-student-name="Kabila Bofotola Aline"', false)
        ->assertSee('data-student-matricule="EL-2026-0001"', false)
        ->assertSee('data-student-class="3e A"', false)
        ->assertSee('data-student-cycle="Secondaire"', false)
        ->assertSee('storage/eleves/aline.jpg', false)
        ->assertSee(route('admin.eleves.edit', $eleve), false)
        ->assertSee(route('admin.eleves.show', $eleve), false);
});