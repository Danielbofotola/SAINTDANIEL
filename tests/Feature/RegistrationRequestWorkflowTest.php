<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'demandes_comptes', 'demandes_inscription', 'inscriptions', 'eleve_sante', 'eleves',
        'tuteurs', 'classes', 'cycles', 'annees_scolaires', 'professeurs', 'users',
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
    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('matricule');
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('sexe');
        $table->string('specialite');
        $table->string('telephone');
        $table->string('email');
        $table->string('photo')->nullable();
        $table->string('statut');
    });
    Schema::create('cycles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('statut');
    });
    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->unsignedBigInteger('section_id')->nullable();
        $table->unsignedBigInteger('option_id')->nullable();
        $table->string('nom');
        $table->string('statut');
    });
    Schema::create('annees_scolaires', function (Blueprint $table) {
        $table->id();
        $table->string('libelle');
        $table->date('date_debut');
        $table->date('date_fin');
        $table->boolean('is_encours')->default(false);
        $table->string('statut');
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
    Schema::create('demandes_inscription', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('sexe');
        $table->date('date_naissance');
        $table->string('lieu_naissance')->nullable();
        $table->unsignedBigInteger('classe_id')->nullable();
        $table->unsignedBigInteger('annee_scolaire_id')->nullable();
        $table->string('nom_mere');
        $table->string('telephone_mere');
        $table->string('email_mere')->nullable();
        $table->text('adresse_mere')->nullable();
        $table->string('nom_pere')->nullable();
        $table->string('telephone_pere')->nullable();
        $table->string('email_pere')->nullable();
        $table->string('statut')->default('en_attente');
        $table->unsignedBigInteger('traite_par')->nullable();
        $table->timestamp('traite_le')->nullable();
        $table->text('motif_refus')->nullable();
        $table->timestamps();
    });
    Schema::create('demandes_comptes', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('username');
        $table->string('email');
        $table->string('telephone')->nullable();
        $table->string('role_souhaite');
        $table->string('mot_de_passe')->nullable();
        $table->string('statut')->default('en_attente');
        $table->unsignedBigInteger('traite_par')->nullable();
        $table->timestamp('traite_le')->nullable();
        $table->text('motif_refus')->nullable();
        $table->timestamps();
    });

    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'nom' => '3e A', 'statut' => 'actif']);
    DB::table('annees_scolaires')->insert([
        'id' => 1,
        'libelle' => '2026-2027',
        'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-01',
        'is_encours' => true,
        'statut' => 'actif',
    ]);
});

function makeAdminForRegistrationRequests(): User
{
    return User::create([
        'username' => 'registration-admin',
        'email' => 'registration-admin@example.test',
        'password' => 'admin-password',
        'role' => 'admin',
        'statut' => 'actif',
    ]);
}

function validStudentRequest(): array
{
    return [
        'nom' => 'Mwamba',
        'postnom' => 'Kabila',
        'prenom' => 'Aline',
        'sexe' => 'F',
        'date_naissance' => '2014-05-12',
        'lieu_naissance' => 'Kinshasa',
        'classe_id' => 1,
        'annee_scolaire_id' => 1,
        'nom_mere' => 'Marie Kabila',
        'telephone_mere' => '0990000000',
        'email_mere' => 'marie@example.test',
    ];
}

test('a public student application remains pending until an admin approves enrollment', function () {
    $this->post(route('registration.student.store'), validStudentRequest())
        ->assertRedirect(route('registration.student.create'))
        ->assertSessionHas('success');

    expect(DB::table('demandes_inscription')->where('statut', 'en_attente')->count())->toBe(1)
        ->and(DB::table('eleves')->count())->toBe(0)
        ->and(DB::table('inscriptions')->count())->toBe(0);

    $admin = makeAdminForRegistrationRequests();
    $applicationId = DB::table('demandes_inscription')->value('id');

    $this->actingAs($admin)
        ->get(route('admin.registration.students'))
        ->assertOk()
        ->assertSee('Mwamba Kabila Aline');

    $this->post(route('admin.registration.students.approve', $applicationId), [
        'classe_id' => 1,
        'annee_scolaire_id' => 1,
    ])->assertRedirect(route('admin.registration.students'));

    $application = DB::table('demandes_inscription')->where('id', $applicationId)->first();
    $student = DB::table('eleves')->first();
    expect($application->statut)->toBe('acceptee')
        ->and($application->traite_par)->toBe($admin->id)
        ->and($student->nom)->toBe('Mwamba')
        ->and(DB::table('inscriptions')->where('eleve_id', $student->id)->count())->toBe(1);
});

test('a requested account is not active until the admin approves it', function () {
    $this->post(route('registration.account.store'), [
        'nom' => 'Kabila',
        'prenom' => 'Aline',
        'username' => 'aline.prof',
        'email' => 'aline.prof@example.test',
        'telephone' => '0990000000',
        'role_souhaite' => 'professeur',
        'mot_de_passe' => 'secret1234',
        'mot_de_passe_confirmation' => 'secret1234',
    ])->assertRedirect(route('registration.account.create'));

    $application = DB::table('demandes_comptes')->first();
    expect(Hash::check('secret1234', $application->mot_de_passe))->toBeTrue()
        ->and(DB::table('users')->count())->toBe(0);

    DB::table('professeurs')->insert([
        'id' => 1,
        'matricule' => 'PROF-2026-0001',
        'nom' => 'Kabila',
        'prenom' => 'Aline',
        'sexe' => 'F',
        'specialite' => 'Mathématiques',
        'telephone' => '0990000000',
        'email' => 'aline.prof@example.test',
        'statut' => 'actif',
    ]);

    $admin = makeAdminForRegistrationRequests();
    $this->actingAs($admin)
        ->get(route('admin.registration.accounts'))
        ->assertOk()
        ->assertSee('aline.prof');

    $this->post(route('admin.registration.accounts.approve', $application->id), [
        'role' => 'professeur',
        'professeur_id' => 1,
    ])->assertRedirect(route('admin.registration.accounts'));

    $createdUser = DB::table('users')->where('username', 'aline.prof')->first();
    $processedApplication = DB::table('demandes_comptes')->where('id', $application->id)->first();
    expect($createdUser->role)->toBe('professeur')
        ->and($createdUser->statut)->toBe('actif')
        ->and($createdUser->professeur_id)->toBe(1)
        ->and(Hash::check('secret1234', $createdUser->password))->toBeTrue()
        ->and($processedApplication->statut)->toBe('acceptee')
        ->and($processedApplication->mot_de_passe)->toBeNull();
});

test('an admin can reject both request types without creating student or user records', function () {
    $this->post(route('registration.student.store'), validStudentRequest());
    $studentRequestId = DB::table('demandes_inscription')->value('id');

    $this->post(route('registration.account.store'), [
        'nom' => 'Mwamba',
        'prenom' => 'Jean',
        'username' => 'jean.mwamba',
        'email' => 'jean.mwamba@example.test',
        'role_souhaite' => 'comptable',
        'mot_de_passe' => 'secret1234',
        'mot_de_passe_confirmation' => 'secret1234',
    ]);
    $accountRequestId = DB::table('demandes_comptes')->value('id');

    $admin = makeAdminForRegistrationRequests();
    $this->actingAs($admin)
        ->post(route('admin.registration.students.reject', $studentRequestId), ['motif_refus' => 'Dossier incomplet'])
        ->assertRedirect();
    $this->post(route('admin.registration.accounts.reject', $accountRequestId), ['motif_refus' => 'Rôle non confirmé'])
        ->assertRedirect();

    expect(DB::table('demandes_inscription')->where('id', $studentRequestId)->value('statut'))->toBe('refusee')
        ->and(DB::table('demandes_comptes')->where('id', $accountRequestId)->value('statut'))->toBe('refusee')
        ->and(DB::table('eleves')->count())->toBe(0)
        ->and(DB::table('users')->where('role', '!=', 'admin')->count())->toBe(0);
});

test('a public student application without a school year is attached to the current year', function () {
    $this->post(route('registration.student.store'), collect(validStudentRequest())->except('annee_scolaire_id')->all())
        ->assertRedirect(route('registration.student.create'))
        ->assertSessionHas('success');

    expect((int) DB::table('demandes_inscription')->value('annee_scolaire_id'))
        ->toBe((int) DB::table('annees_scolaires')->where('is_encours', true)->value('id'));
});

test('a public student application is rejected when no school year is current', function () {
    DB::table('annees_scolaires')->update(['is_encours' => false]);

    $this->post(route('registration.student.store'), collect(validStudentRequest())->except('annee_scolaire_id')->all())
        ->assertSessionHasErrors('annee_scolaire_id');

    expect(DB::table('demandes_inscription')->count())->toBe(0);
});

test('an admin enrollment without a school year is attached to the current year', function () {
    $admin = makeAdminForRegistrationRequests();

    $this->actingAs($admin)
        ->post(route('admin.eleves.store'), [
            'classe_id' => 1,
            'nom' => 'Mwamba',
            'postnom' => 'Kabila',
            'prenom' => 'Aline',
            'sexe' => 'F',
            'date_naissance' => '2014-05-12',
            'lieu_naissance' => 'Kinshasa',
            'nom_mere' => 'Marie Kabila',
            'telephone_mere' => '0990000000',
        ])
        ->assertRedirect();

    expect((int) DB::table('inscriptions')->value('annee_scolaire_id'))
        ->toBe((int) DB::table('annees_scolaires')->where('is_encours', true)->value('id'));
});

test('a school year submitted by the client is ignored in favour of the current year', function () {
    DB::table('annees_scolaires')->insert([
        'id' => 2,
        'libelle' => '2025-2026',
        'date_debut' => '2025-09-01',
        'date_fin' => '2026-07-01',
        'is_encours' => false,
        'statut' => 'actif',
    ]);

    $this->post(route('registration.student.store'), array_merge(validStudentRequest(), ['annee_scolaire_id' => 2]))
        ->assertRedirect(route('registration.student.create'))
        ->assertSessionHas('success');

    expect((int) DB::table('demandes_inscription')->value('annee_scolaire_id'))->toBe(1);
});
