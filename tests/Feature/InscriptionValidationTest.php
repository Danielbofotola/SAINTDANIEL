<?php

use App\Models\TypeFrais;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'paiements', 'frais_scolaires', 'types_frais', 'periodes', 'documents_eleves', 'eleve_sante',
        'inscriptions', 'eleves', 'tuteurs', 'classes', 'salles', 'cycles', 'annees_scolaires', 'users',
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
        $table->string('username')->unique();
        $table->string('email')->unique();
        $table->string('telephone', 20)->nullable();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->rememberToken();
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
    Schema::create('tuteurs', function (Blueprint $table) {
        $table->id();
        $table->string('nom_mere');
        $table->string('telephone_mere');
        $table->string('profession_mere')->nullable();
        $table->string('email_mere')->nullable();
        $table->text('adresse_mere')->nullable();
        $table->string('nom_pere')->nullable();
        $table->string('telephone_pere')->nullable();
        $table->string('profession_pere')->nullable();
        $table->string('email_pere')->nullable();
        $table->text('adresse_pere')->nullable();
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
        $table->string('photo')->nullable();
        $table->unsignedBigInteger('tuteur_id');
        $table->string('statut');
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
    Schema::create('types_frais', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 100);
        $table->text('description')->nullable();
        $table->boolean('est_inscription')->default(false);
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('frais_scolaires', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('type_frais_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->decimal('montant_exige', 10, 2);
        $table->date('date_echeance');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('paiements', function (Blueprint $table) {
        $table->id();
        $table->string('num_recu', 50)->unique();
        $table->unsignedBigInteger('inscription_id');
        $table->unsignedBigInteger('frais_scolaire_id');
        $table->unsignedBigInteger('periode_id')->nullable();
        $table->decimal('montant_paye', 10, 2);
        $table->dateTime('date_paiement')->nullable();
        $table->string('mode_paiement')->default('especes');
        $table->string('reference_transaction', 100)->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif',
    ]);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'nom' => '3e A', 'statut' => 'actif']);

    $tuteurId = DB::table('tuteurs')->insertGetId([
        'nom_mere' => 'Marie', 'telephone_mere' => '0990', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $eleveId = DB::table('eleves')->insertGetId([
        'matricule' => 'ELV-2026-0001', 'nom' => 'Mwamba', 'postnom' => 'T', 'prenom' => 'Aline', 'sexe' => 'F',
        'date_naissance' => '2014-05-12', 'tuteur_id' => $tuteurId, 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('inscriptions')->insert([
        'id' => 1, 'eleve_id' => $eleveId, 'classe_id' => 1, 'annee_scolaire_id' => 1,
        'date_inscription' => '2026-09-05', 'statut' => 'en_attente', 'created_at' => now(), 'updated_at' => now(),
    ]);

    // Tarif « frais d'inscription » (marqué) et tarif ordinaire.
    $typeInscription = DB::table('types_frais')->insertGetId(['nom' => 'Frais d’inscription', 'est_inscription' => 1, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $typeScolaire = DB::table('types_frais')->insertGetId(['nom' => 'Frais scolaires', 'est_inscription' => 0, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('frais_scolaires')->insert([
        ['id' => 1, 'type_frais_id' => $typeInscription, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'montant_exige' => 50, 'date_echeance' => '2026-10-01', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'type_frais_id' => $typeScolaire, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'montant_exige' => 150, 'date_echeance' => '2026-10-31', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()],
    ]);
});

test('a pending inscription is validated when the cashier encashes the registration fee', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->post(route('comptable.paiements.store'), [
            'inscription_id' => 1, 'frais_scolaire_id' => 1,
            'montant_paye' => 50, 'mode_paiement' => 'especes',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', fn (string $message) => str_contains($message, 'validée automatiquement'));

    expect(DB::table('inscriptions')->where('id', 1)->value('statut'))->toBe('actif');
});

test('paying a regular fee does not validate the inscription', function () {
    $comptable = User::factory()->create(['role' => 'comptable']);

    $this->actingAs($comptable)
        ->post(route('comptable.paiements.store'), [
            'inscription_id' => 1, 'frais_scolaire_id' => 2,
            'montant_paye' => 75, 'mode_paiement' => 'especes',
        ])
        ->assertRedirect();

    expect(DB::table('inscriptions')->where('id', 1)->value('statut'))->toBe('en_attente');
});

test('the admin payment page also validates the inscription', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.paiements.store'), [
            'inscription_id' => 1, 'frais_scolaire_id' => 1,
            'montant_paye' => 50, 'mode_paiement' => 'banque',
        ])
        ->assertRedirect();

    expect(DB::table('inscriptions')->where('id', 1)->value('statut'))->toBe('actif');
});

test('the registration fee type is detected by flag or by name', function () {
    expect((new TypeFrais(['nom' => 'Frais d’inscription']))->estFraisInscription())->toBeTrue()
        ->and((new TypeFrais(['nom' => 'Frais scolaires']))->estFraisInscription())->toBeFalse()
        ->and((new TypeFrais(['nom' => 'Droit d’entrée', 'est_inscription' => true]))->estFraisInscription())->toBeTrue();
});

test('the enrolment form creates a pending inscription awaiting payment', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.eleves.store'), [
            'classe_id' => 1,
            'nom' => 'Kasongo', 'postnom' => 'B', 'prenom' => 'Divine',
            'sexe' => 'F', 'date_naissance' => '2013-03-10',
            'nom_mere' => 'Jeanne', 'telephone_mere' => '0810',
        ])
        ->assertRedirect();

    expect(DB::table('inscriptions')->where('eleve_id', DB::table('eleves')->max('id'))->value('statut'))->toBe('en_attente');
});
