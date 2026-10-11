<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'bulletins', 'paiements', 'frais_scolaires', 'type_frais', 'documents_eleves', 'eleve_sante', 'demandes_inscription', 'inscriptions', 'eleves', 'tuteurs', 'classes',
        'cycles', 'salles', 'annees_scolaires', 'professeurs', 'users',
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
        $table->string('statut');
        $table->rememberToken();
        $table->timestamps();
    });
    Schema::create('bulletins', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('inscription_id');
        $table->unsignedBigInteger('periode_id')->nullable();
        $table->decimal('total_points', 8, 2)->default(0);
        $table->decimal('max_possible', 8, 2)->default(0);
        $table->decimal('pourcentage', 5, 2)->default(0);
        $table->unsignedInteger('place_classe')->nullable();
        $table->string('mention')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('type_frais', function (Blueprint $table) {
        $table->id();
        $table->string('libelle')->nullable();
        $table->string('nom')->nullable();
    });
    Schema::create('frais_scolaires', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('type_frais_id')->nullable();
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->decimal('montant_exige', 10, 2)->default(0);
        $table->date('date_echeance')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('paiements', function (Blueprint $table) {
        $table->id();
        $table->string('num_recu')->nullable();
        $table->unsignedBigInteger('inscription_id');
        $table->unsignedBigInteger('frais_scolaire_id')->nullable();
        $table->unsignedBigInteger('periode_id')->nullable();
        $table->decimal('montant_paye', 10, 2)->default(0);
        $table->timestamp('date_paiement')->nullable();
        $table->string('mode_paiement')->nullable();
        $table->string('reference_transaction')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('eleve_sante', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->string('groupe_sanguin')->nullable();
        $table->text('allergies_pathologies')->nullable();
        $table->text('traitements_urgences')->nullable();
        $table->string('medecin_referent')->nullable();
        $table->timestamps();
    });
    Schema::create('documents_eleves', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->string('type_document');
        $table->string('chemin_fichier');
        $table->string('statut')->default('actif');
        $table->timestamp('created_at')->nullable();
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
        $table->unsignedBigInteger('section_id')->nullable();
        $table->unsignedBigInteger('option_id')->nullable();
        $table->string('nom');
        $table->string('statut');
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

    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);
    DB::table('salles')->insert(['id' => 1, 'nom' => 'Salle 1', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'salle_id' => 1, 'nom' => '3e A', 'statut' => 'actif']);
    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif',
    ]);
});

function seedEleve(array $overrides = []): int
{
    $tuteurId = DB::table('tuteurs')->insertGetId([
        'nom_mere' => 'Marie Kabila', 'telephone_mere' => '0990000000',
        'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $eleveId = DB::table('eleves')->insertGetId(array_merge([
        'matricule' => 'ELV-2026-0001',
        'nom' => 'Mwamba', 'postnom' => 'Kabila', 'prenom' => 'Aline',
        'sexe' => 'F', 'date_naissance' => '2014-05-12', 'lieu_naissance' => 'Kinshasa',
        'tuteur_id' => $tuteurId, 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ], $overrides));

    DB::table('inscriptions')->insert([
        'eleve_id' => $eleveId, 'classe_id' => 1, 'annee_scolaire_id' => 1,
        'date_inscription' => '2026-09-05', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return $eleveId;
}

test('the student list page shows enrollments, class and statistics', function () {
    seedEleve();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.eleves.index'))
        ->assertOk()
        ->assertSee('Liste des élèves')
        ->assertSee('Mwamba Kabila Aline')
        ->assertSee('ELV-2026-0001')
        ->assertSee('3e A')
        ->assertSee('Inscrits cette année');
});

test('désactiver un élève le rend invisible et l’activation le rétablit', function () {
    $eleveId = seedEleve();
    $admin = User::factory()->create(['role' => 'admin']);

    // Désactivation : l'élève sort des effectifs et des écrans de suivi.
    $this->actingAs($admin)->patch(route('admin.eleves.toggle', $eleveId))
        ->assertRedirect()
        ->assertSessionHas('success', fn ($m) => str_contains($m, 'désactivé'));

    expect(\App\Models\Eleve::find($eleveId)->statut)->toBe('inactif')
        ->and(\App\Models\Eleve::inscritAnneeEnCours()->whereKey($eleveId)->exists())->toBeFalse();

    // Son dossier reste consultable : le suivi administratif continue.
    $this->actingAs($admin)->get(route('admin.eleves.show', $eleveId))->assertOk()
        ->assertSee("Dossier de l'élève", false)
        ->assertSee('Responsables légaux');

    // Réactivation : il réapparaît dans les listes et les effectifs.
    $this->actingAs($admin)->patch(route('admin.eleves.toggle', $eleveId))->assertRedirect();

    expect(\App\Models\Eleve::find($eleveId)->statut)->toBe('actif')
        ->and(\App\Models\Eleve::inscritAnneeEnCours()->whereKey($eleveId)->exists())->toBeTrue();
});

test('the student list can be filtered by search term and by status', function () {
    seedEleve();
    seedEleve(['matricule' => 'ELV-2026-0002', 'nom' => 'Ilunga', 'postnom' => 'Mukendi', 'prenom' => 'David', 'sexe' => 'M', 'statut' => 'inactif']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.eleves.index', ['q' => 'Ilunga']))
        ->assertOk()
        ->assertSee('Ilunga Mukendi David')
        ->assertDontSee('Mwamba Kabila Aline');

    $this->actingAs($admin)
        ->get(route('admin.eleves.index', ['statut' => 'inactif']))
        ->assertOk()
        ->assertSee('Ilunga Mukendi David')
        ->assertDontSee('Mwamba Kabila Aline');
});

test('the enrollment history lists processed applications and hides pending ones', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $rows = [
        ['Acceptee', 'F', 'acceptee', 1, 1, null],
        ['Refusee', 'M', 'refusee', 1, 1, 'Dossier incomplet'],
        ['EnAttente', 'M', 'en_attente', null, null, null],
    ];

    foreach ($rows as [$nom, $sexe, $statut, $classeId, $anneeId, $motif]) {
        DB::table('demandes_inscription')->insert([
            'nom' => $nom, 'postnom' => 'Cas', 'prenom' => 'Cas', 'sexe' => $sexe,
            'date_naissance' => '2014-01-01', 'lieu_naissance' => null,
            'classe_id' => $classeId, 'annee_scolaire_id' => $anneeId,
            'nom_mere' => 'Marie', 'telephone_mere' => '0990000000',
            'email_mere' => null, 'adresse_mere' => null,
            'nom_pere' => null, 'telephone_pere' => null, 'email_pere' => null,
            'statut' => $statut, 'traite_par' => $statut === 'en_attente' ? null : $admin->id,
            'traite_le' => $statut === 'en_attente' ? null : now(),
            'motif_refus' => $motif, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.registration.students.history'))
        ->assertOk()
        ->assertSee("Historique d'inscription", false)
        ->assertSee('Acceptee Cas Cas')
        ->assertSee('Refusee Cas Cas')
        ->assertSee('Dossier incomplet')
        ->assertDontSee('EnAttente Cas Cas');

    $this->actingAs($admin)
        ->get(route('admin.registration.students.history', ['statut' => 'refusee']))
        ->assertOk()
        ->assertSee('Refusee Cas Cas')
        ->assertDontSee('Acceptee Cas Cas');
});

test('the admin dashboard menu links to the student list and the enrollment history', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.eleves.index'), false)
        ->assertSee(route('admin.registration.students.history'), false);
});

test('la liste et les statistiques ne comptent que les élèves inscrits dans l’année', function () {
    seedEleve();   // Mwamba, inscrit en 2026-2027

    // Un élève sans aucune inscription dans l'année : ni listé, ni compté.
    $tuteurId = DB::table('tuteurs')->insertGetId([
        'nom_mere' => 'Sans', 'telephone_mere' => '0990000001', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('eleves')->insert([
        'matricule' => 'ELV-2026-9999', 'nom' => 'SansInscription', 'postnom' => 'X', 'prenom' => 'Y',
        'sexe' => 'M', 'date_naissance' => '2014-01-01', 'tuteur_id' => $tuteurId, 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.eleves.index'))
        ->assertOk()
        ->assertSee('Mwamba Kabila Aline')
        ->assertDontSee('SansInscription')
        ->assertSee('<h4>Élèves</h4><p><b>1</b></p>', false);
});

test('la carte de l’élève affiche le QR unique, le matricule et la classe', function () {
    seedEleve();
    $admin = User::factory()->create(['role' => 'admin']);
    $eleve = \App\Models\Eleve::firstOrFail();

    $this->actingAs($admin)
        ->get(route('admin.eleves.carte', $eleve))
        ->assertOk()
        ->assertSee('Carte d’élève', false)
        ->assertSee('id="carte-qr"', false)
        ->assertSee('js/qrcode.min.js', false)
        ->assertSee('ELV-2026-0001')
        ->assertSee('3e A');
});
