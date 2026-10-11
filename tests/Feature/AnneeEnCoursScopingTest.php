<?php

use App\Models\Eleve;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    foreach (['bulletins', 'paiements', 'frais_scolaires', 'type_frais', 'inscriptions', 'eleves', 'classes', 'cycles', 'sections', 'options', 'salles', 'annees_scolaires', 'users'] as $table) {
        Schema::dropIfExists($table);
    }

    Schema::create('bulletins', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('inscription_id');
        $table->unsignedBigInteger('periode_id')->nullable();
        $table->decimal('pourcentage', 5, 2)->default(0);
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
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('username', 50)->unique();
        $table->string('email', 100)->unique();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('annees_scolaires', function (Blueprint $table) {
        $table->id();
        $table->string('libelle', 20)->unique();
        $table->date('date_debut');
        $table->date('date_fin');
        $table->boolean('is_encours')->default(false);
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });

    Schema::create('cycles', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 50);
        $table->string('statut')->default('actif');
    });

    Schema::create('sections', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 50)->nullable();
    });

    Schema::create('options', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 50)->nullable();
    });

    Schema::create('salles', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 50)->nullable();
    });

    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 50);
        $table->unsignedBigInteger('cycle_id');
        $table->unsignedBigInteger('section_id')->nullable();
        $table->unsignedBigInteger('option_id')->nullable();
        $table->unsignedBigInteger('salle_id')->nullable();
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
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('inscriptions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->date('date_inscription');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('tuteurs', function (Blueprint $table) {
        $table->id();
        $table->string('nom_mere')->nullable();
        $table->string('telephone_mere')->nullable();
        $table->string('email_mere')->nullable();
        $table->text('adresse_mere')->nullable();
        $table->string('nom_pere')->nullable();
        $table->string('telephone_pere')->nullable();
        $table->string('email_pere')->nullable();
        $table->text('adresse_pere')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('eleve_sante', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->string('groupe_sanguin')->nullable();
        $table->string('allergies_pathologies')->nullable();
        $table->string('traitements_urgences')->nullable();
        $table->string('medecin_referent')->nullable();
        $table->timestamps();
    });

    Schema::create('documents_eleves', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->string('type_document');
        $table->string('chemin_fichier');
        $table->string('statut')->default('actif');
        $table->timestamp('created_at')->useCurrent();
    });

    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Primaire', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'nom' => '3e primaire', 'cycle_id' => 1, 'statut' => 'actif']);

    // Année 1 : ancienne (avec un élève déjà inscrit l'an dernier)
    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2025-2026', 'date_debut' => '2025-09-01', 'date_fin' => '2026-07-01',
        'is_encours' => false, 'statut' => 'actif',
    ]);
    // Année 2 : année en cours
    DB::table('annees_scolaires')->insert([
        'id' => 2, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-01',
        'is_encours' => true, 'statut' => 'actif',
    ]);

    DB::table('eleves')->insert([
        ['id' => 1, 'matricule' => 'ELV-2025-001', 'nom' => 'Ancien', 'prenom' => 'Eleve', 'sexe' => 'M', 'date_naissance' => '2015-01-01', 'statut' => 'actif'],
        ['id' => 2, 'matricule' => 'ELV-2026-001', 'nom' => 'Courant', 'prenom' => 'Eleve', 'sexe' => 'F', 'date_naissance' => '2016-01-01', 'statut' => 'actif'],
    ]);

    // L'élève 1 n'est inscrit que l'an dernier, l'élève 2 seulement cette année
    DB::table('inscriptions')->insert([
        ['eleve_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2025-09-05', 'statut' => 'actif'],
        ['eleve_id' => 2, 'classe_id' => 1, 'annee_scolaire_id' => 2, 'date_inscription' => '2026-09-05', 'statut' => 'actif'],
    ]);
});

test('the inscription scope keeps only the current school year', function () {
    expect(Inscription::anneeEnCours()->count())->toBe(1)
        ->and(Inscription::anneeEnCours()->first()->eleve_id)->toBe(2);
});

test('the student scope keeps only pupils enrolled for the current school year', function () {
    expect(Eleve::inscritAnneeEnCours()->pluck('id')->all())->toBe([2]);
});

test('the class students page lists only the current school year enrolments', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.classes.students', 1))
        ->assertOk()
        ->assertSee('Courant')
        ->assertDontSee('Ancien');
});

test('the class list counts only the current school year enrolments', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.classes.index'))
        ->assertOk()
        ->assertSee('1 élève(s)');
});

test('without a current school year the scopes return nothing', function () {
    DB::table('annees_scolaires')->update(['is_encours' => false]);

    expect(Inscription::anneeEnCours()->count())->toBe(0)
        ->and(Eleve::inscritAnneeEnCours()->count())->toBe(0);
});

test('a pupil file shows the current school year enrolment', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.eleves.show', 2))
        ->assertOk()
        ->assertSee('3e primaire')
        ->assertDontSee("n'est pas inscrit pour l'année en cours", false);
});

test('a pupil file states when the pupil is not enrolled for the current year', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.eleves.show', 1))
        ->assertOk()
        ->assertSee("n'est pas inscrit pour l'année en cours", false)
        // Le dossier montre désormais l'historique complet : l'ancienne
        // classe reste visible, mais l'année en cours est bien déclarée vide.
        ->assertSee('Parcours')
        ->assertSee('3e primaire');
});
