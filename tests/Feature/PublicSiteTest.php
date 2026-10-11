<?php

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Cycle;
use App\Models\MessageContact;
use App\Models\ParametreSysteme;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Partie publique (vitrine) : pages d'accueil, d'inscriptions, à propos et contact,
 * accessibles sans authentification.
 */
beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'contact_messages', 'inscriptions', 'eleves', 'classes', 'options', 'cycles',
        'annees_scolaires', 'parametres_systeme', 'users', 'frais_scolaires',
    ] as $table) {
        Schema::dropIfExists($table);
    }
    Schema::enableForeignKeyConstraints();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('username')->unique();
        $table->string('email')->unique();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut')->default('actif');
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('parametres_systeme', function (Blueprint $table) {
        $table->id();
        $table->string('nom_etablissement', 150)->nullable();
        $table->string('nom_court', 60)->nullable();
        $table->string('telephone', 30)->nullable();
        $table->string('email', 150)->nullable();
        $table->text('adresse')->nullable();
        $table->boolean('portail_parents_actif')->default(false);
        $table->timestamps();
    });

    Schema::create('annees_scolaires', function (Blueprint $table) {
        $table->id();
        $table->string('libelle');
        $table->date('date_debut')->nullable();
        $table->date('date_fin')->nullable();
        $table->boolean('is_encours')->default(false);
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('cycles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('options', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->timestamps();
    });

    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->unsignedBigInteger('section_id')->nullable();
        $table->unsignedBigInteger('option_id')->nullable();
        $table->string('nom');
        $table->integer('ordre')->default(0);
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('eleves', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->timestamps();
    });

    Schema::create('inscriptions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->date('date_inscription')->nullable();
        $table->string('statut')->default('en_attente');
        $table->timestamps();
    });

    Schema::create('frais_scolaires', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('type_frais_id')->nullable();
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->decimal('montant_exige', 10, 2);
        $table->string('devise', 3)->default('USD');
        $table->date('date_echeance')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('contact_messages', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 100);
        $table->string('email', 100);
        $table->string('telephone', 20)->nullable();
        $table->string('sujet', 120);
        $table->text('message');
        $table->string('statut', 20)->default('nouveau');
        $table->dateTime('traite_le')->nullable();
        $table->unsignedBigInteger('traite_par')->nullable();
        $table->string('adresse_ip', 45)->nullable();
        $table->timestamps();
    });

    ParametreSysteme::create([
        'nom_etablissement' => 'COMPLEXE SCOLAIRE BABAZIN',
        'nom_court' => 'C.S. Moty',
        'telephone' => '+243 900 123 456',
        'email' => 'contact@babazin.example',
        'adresse' => 'Avenue de la Paix, Kinshasa',
    ]);

    $annee = AnneeScolaire::create([
        'libelle' => '2026-2027',
        'is_encours' => true,
        'statut' => 'actif',
    ]);

    $cycle = Cycle::create(['nom' => 'Secondaire général', 'statut' => 'actif']);
    $classe = Classe::create(['cycle_id' => $cycle->id, 'nom' => '1ère A', 'statut' => 'actif']);

    DB::table('inscriptions')->insert([
        'eleve_id' => 1,
        'classe_id' => $classe->id,
        'annee_scolaire_id' => $annee->id,
        'statut' => 'actif',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('frais_scolaires')->insert([
        'classe_id' => $classe->id,
        'annee_scolaire_id' => $annee->id,
        'montant_exige' => 300,
        'devise' => 'USD',
        'statut' => 'actif',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

test('les pages publiques sont accessibles sans authentification', function () {
    $this->get(route('site.accueil'))
        ->assertOk()
        ->assertSee('COMPLEXE SCOLAIRE BABAZIN')
        ->assertSee('Demander une inscription')
        ->assertSee('1ère A');

    $this->get(route('site.inscriptions'))
        ->assertOk()
        ->assertSee('Conditions d’admission')
        ->assertSee('2026-2027');

    $this->get(route('site.apropos'))
        ->assertOk()
        ->assertSee('Secondaire général');

    $this->get(route('site.contact'))
        ->assertOk()
        ->assertSee('contact@babazin.example')
        ->assertSee('Frais scolaires et paiements');
});

test('les pages publiques signalent la fermeture des inscriptions sans année ouverte', function () {
    AnneeScolaire::query()->update(['is_encours' => false]);
    Classe::query()->update(['statut' => 'inactif']);

    $this->get(route('site.inscriptions'))
        ->assertOk()
        ->assertSee('Les demandes d’inscription ne sont pas encore ouvertes');
});

test('le formulaire de contact enregistre le message et affiche une référence', function () {
    $response = $this->post(route('site.contact.store'), [
        'nom' => 'Marie Kabila',
        'email' => 'marie@example.com',
        'telephone' => '+243 810 000 111',
        'sujet' => 'Inscription et admission',
        'message' => 'Bonjour, je souhaite inscrire ma fille en 1ère A pour la prochaine rentrée.',
    ]);

    $response->assertRedirect(route('site.contact'));
    $response->assertSessionHas('success');

    $message = MessageContact::sole();
    expect($message->nom)->toBe('Marie Kabila')
        ->and($message->statut)->toBe(MessageContact::STATUT_NOUVEAU)
        ->and($message->reference())->toBe('MSG-000001');
});

test('le formulaire de contact refuse les données incomplètes', function () {
    $this->from(route('site.contact'))
        ->post(route('site.contact.store'), ['nom' => '', 'email' => 'pas-un-email', 'sujet' => '', 'message' => 'court'])
        ->assertRedirect(route('site.contact'))
        ->assertSessionHasErrors(['nom', 'email', 'sujet', 'message']);

    expect(MessageContact::count())->toBe(0);
});

test('le piège anti-robot ignore les envois automatisés', function () {
    $this->post(route('site.contact.store'), [
        'site_web' => 'http://spam.example',
        'nom' => 'Robot',
        'email' => 'robot@example.com',
        'sujet' => 'Autre demande',
        'message' => 'Ce message ne doit jamais être enregistré en base de données.',
    ])->assertRedirect(route('site.contact'));

    expect(MessageContact::count())->toBe(0);
});

test('l’administration consulte et traite les messages de contact', function () {
    $message = MessageContact::create([
        'nom' => 'Parent Test',
        'email' => 'parent@example.com',
        'sujet' => 'Frais scolaires et paiements',
        'message' => 'Quel est le montant des frais pour la 2ème année ?',
        'statut' => MessageContact::STATUT_NOUVEAU,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $otherRole = User::factory()->create(['role' => 'professeur']);
    $this->actingAs($otherRole)->get(route('admin.messages-contact.index'))->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.messages-contact.index'))
        ->assertOk()
        ->assertSee('Parent Test')
        ->assertSee($message->reference());

    $this->actingAs($admin)
        ->patch(route('admin.messages-contact.statut', $message), ['statut' => MessageContact::STATUT_TRAITE])
        ->assertRedirect();

    expect($message->fresh()->statut)->toBe(MessageContact::STATUT_TRAITE)
        ->and($message->fresh()->traite_par)->toBe($admin->id);

    $this->actingAs($admin)
        ->delete(route('admin.messages-contact.destroy', $message))
        ->assertRedirect();

    expect(MessageContact::count())->toBe(0);
});

test('la vitrine est la page d’ouverture du système et la connexion vit sur /connexion', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Demander une inscription')
        ->assertDontSee('name="password"', false);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('CONNEXION')
        ->assertSee('name="password"', false);
});

test('les anciennes adresses de la vitrine sont redirigées de façon permanente', function () {
    $this->get('/site')->assertStatus(301)->assertRedirect('/');
    $this->get('/site/inscriptions')->assertStatus(301)->assertRedirect('/inscriptions');
    $this->get('/site/a-propos')->assertStatus(301)->assertRedirect('/a-propos');
    $this->get('/site/contact')->assertStatus(301)->assertRedirect('/contact');
});

test('les pages de démarches et de connexion reprennent le design de la vitrine', function () {
    $pages = [
        route('registration.student.create'),
        route('registration.account.create'),
        route('login'),
        route('login.pin'),
    ];

    foreach ($pages as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee('site-assets/styles.css', false)   // même feuille de style que la vitrine
            ->assertSee('À propos')                        // menu public commun
            ->assertSee('Démarches & contact');            // pied de page commun
    }
});

test('aucun messager flottant n’est injecté sur les pages publiques, même en étant connecté', function () {
    $prefet = User::factory()->create(['role' => 'prefetture']);

    $pages = [
        route('site.accueil'),
        route('site.inscriptions'),
        route('site.apropos'),
        route('site.contact'),
        route('registration.student.create'),
        route('registration.account.create'),
    ];

    foreach ($pages as $url) {
        $this->actingAs($prefet)
            ->get($url)
            ->assertOk()
            ->assertDontSee('<a class="messagerie-flottante', false);
    }
});

test('la page publique des inscriptions affiche les frais réels du système', function () {
    $this->get(route('site.inscriptions'))
        ->assertOk()
        ->assertSee('Frais scolaires définis par l’école')
        ->assertSee('1ère A')
        ->assertSee('300,00 $');
});
