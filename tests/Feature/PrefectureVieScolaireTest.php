<?php

use App\Models\Eleve;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Vie scolaire du préfet : présence/ponctualité, horaires, évaluations,
 * dossiers élèves, tuteurs & parents.
 */
beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'paiements', 'frais_scolaires', 'type_frais', 'bulletins', 'documents_eleves', 'presences', 'notes', 'evaluations', 'periodes', 'emplois_du_temps', 'affectations_cours', 'cours',
        'eleve_sante', 'eleve_user', 'inscriptions', 'eleves', 'tuteurs', 'classes', 'cycles', 'salles',
        'sections', 'options', 'professeurs', 'annees_scolaires', 'users',
    ] as $table) {
        Schema::dropIfExists($table);
    }
    Schema::enableForeignKeyConstraints();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable();
        $table->string('nom', 50)->nullable();
        $table->string('postnom', 50)->nullable();
        $table->string('prenom', 50)->nullable();
        $table->string('photo')->nullable();
        $table->string('username', 50)->unique();
        $table->string('email', 100)->unique();
        $table->string('telephone', 20)->nullable();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->timestamp('last_login')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('matricule')->unique();
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('sexe')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('annees_scolaires', function (Blueprint $table) {
        $table->id();
        $table->string('libelle', 20)->unique();
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
    });

    Schema::create('salles', function (Blueprint $table) {
        $table->id();
        $table->string('nom')->nullable();
        $table->integer('capacite')->nullable();
        $table->string('statut')->default('actif');
    });

    Schema::create('sections', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->timestamps();
    });

    Schema::create('options', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('section_id')->nullable();
        $table->string('nom');
        $table->timestamps();
    });

    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->unsignedBigInteger('section_id')->nullable();
        $table->unsignedBigInteger('option_id')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('tuteurs', function (Blueprint $table) {
        $table->id();
        $table->string('nom_pere')->nullable();
        $table->string('profession_pere')->nullable();
        $table->string('telephone_pere', 20)->nullable();
        $table->string('email_pere')->nullable();
        $table->string('adresse_pere')->nullable();
        $table->string('nom_mere')->nullable();
        $table->string('profession_mere')->nullable();
        $table->string('telephone_mere', 20)->nullable();
        $table->string('email_mere')->nullable();
        $table->string('adresse_mere')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('eleves', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('tuteur_id')->nullable();
        $table->string('matricule')->nullable();
        $table->string('nom', 50);
        $table->string('postnom', 50)->nullable();
        $table->string('prenom', 50);
        $table->string('sexe')->nullable();
        $table->date('date_naissance')->nullable();
        $table->string('lieu_naissance')->nullable();
        $table->string('photo')->nullable();
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

    Schema::create('eleve_user', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('user_id');
        $table->timestamps();
    });

    Schema::create('inscriptions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->date('date_inscription')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('cours', function (Blueprint $table) {
        $table->id();
        $table->string('code')->nullable();
        $table->string('intitule');
        $table->decimal('ponderation', 4, 1)->default(1);
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

    Schema::create('emplois_du_temps', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->string('jour');
        $table->string('heure_debut');
        $table->string('heure_fin');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('periodes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('nom');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('evaluations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
        $table->unsignedBigInteger('periode_id')->nullable();
        $table->string('titre');
        $table->string('type_evaluation')->default('interro');
        $table->decimal('max_note', 5, 2)->default(10);
        $table->date('date_evaluation')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamp('validee_at')->nullable();
        $table->unsignedBigInteger('validee_par')->nullable();
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
        $table->string('chemin_pdf')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamp('approuve_at')->nullable();
        $table->unsignedBigInteger('approuve_par')->nullable();
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

    Schema::create('notes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('evaluation_id');
        $table->unsignedBigInteger('eleve_id');
        $table->decimal('note', 5, 2)->nullable();
        $table->timestamps();
    });

    Schema::create('presences', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id')->nullable();
        $table->unsignedBigInteger('eleve_id');
        $table->string('date_seance');
        $table->string('statut_presence')->default('present');
        $table->string('motif')->nullable();
        $table->unsignedBigInteger('enregistre_par')->nullable();
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

    // --- Données de test ---
    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-01',
        'is_encours' => true, 'statut' => 'actif',
    ]);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Primaire', 'statut' => 'actif']);
    DB::table('salles')->insert(['id' => 1, 'nom' => 'Salle A', 'capacite' => 40, 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'nom' => '3e primaire', 'cycle_id' => 1, 'salle_id' => 1, 'statut' => 'actif']);
    DB::table('professeurs')->insert(['id' => 1, 'matricule' => 'PRF-001', 'nom' => 'Mwamba', 'prenom' => 'Jean', 'statut' => 'actif']);
    DB::table('cours')->insert(['id' => 1, 'code' => 'MATH', 'intitule' => 'Mathématiques', 'ponderation' => 4, 'statut' => 'actif']);
    DB::table('affectations_cours')->insert(['id' => 1, 'professeur_id' => 1, 'cours_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'statut' => 'actif']);
    DB::table('emplois_du_temps')->insert([
        'id' => 1, 'affectation_id' => 1, 'salle_id' => 1, 'jour' => 'lundi',
        'heure_debut' => '08:00', 'heure_fin' => '09:00', 'statut' => 'actif',
    ]);
    DB::table('periodes')->insert(['id' => 1, 'annee_scolaire_id' => 1, 'nom' => '1er trimestre', 'statut' => 'actif']);
    DB::table('evaluations')->insert([
        'id' => 1, 'affectation_id' => 1, 'periode_id' => 1, 'titre' => 'Interro 1',
        'type_evaluation' => 'interro', 'max_note' => 10, 'date_evaluation' => '2026-10-01', 'statut' => 'actif',
    ]);
    DB::table('tuteurs')->insert(['id' => 1, 'nom_pere' => 'Bofotola Daniel', 'telephone_pere' => '0898272942', 'statut' => 'actif']);
    DB::table('eleves')->insert([
        'id' => 1, 'tuteur_id' => 1, 'matricule' => 'ELV-001', 'nom' => 'Banzolo', 'prenom' => 'Aicha',
        'sexe' => 'F', 'date_naissance' => '2017-05-12', 'statut' => 'actif',
    ]);
    DB::table('inscriptions')->insert(['id' => 1, 'eleve_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-05', 'statut' => 'actif']);
    DB::table('presences')->insert([
        'id' => 1, 'affectation_id' => 1, 'eleve_id' => 1, 'date_seance' => now()->toDateString(),
        'statut_presence' => 'retard', 'motif' => 'Transport',
    ]);
    DB::table('documents_eleves')->insert([
        'id' => 1, 'eleve_id' => 1, 'type_document' => 'certificat_medical',
        'chemin_fichier' => 'eleves/documents/certificat.pdf', 'statut' => 'actif', 'created_at' => now(),
    ]);

    User::factory()->create(['role' => 'prefetture']);
    User::factory()->create(['role' => 'comptable']);
    User::factory()->create(['role' => 'parent', 'nom' => 'Bofotola', 'prenom' => 'Daniel']);
});

test('le préfet dispose des pages de vie scolaire', function () {
    $prefet = User::where('role', 'prefetture')->first();

    $this->actingAs($prefet)->get(route('prefecture.presences'))->assertOk()
        ->assertSee('En retard')->assertSee('Enseignants attendus')->assertSee('Transport');

    $this->actingAs($prefet)->get(route('prefecture.horaires'))->assertOk()
        ->assertSee('Mathématiques')->assertSee('Mwamba')->assertSee('Salle A');

    $this->actingAs($prefet)->get(route('prefecture.evaluations'))->assertOk()
        ->assertSee('Interro 1')->assertSee('Interrogation')->assertSee('1er trimestre');

    $this->actingAs($prefet)->get(route('prefecture.eleves'))->assertOk()
        ->assertSee('Aicha')->assertSee('ELV-001')->assertSee('3e primaire');

    $this->actingAs($prefet)->get(route('prefecture.tuteurs'))->assertOk()
        ->assertSee('Bofotola Daniel')->assertSee('0898272942')->assertSee('Comptes parents');
});

test('la fiche élève regroupe identité, santé, inscriptions et assiduité', function () {
    $prefet = User::where('role', 'prefetture')->first();
    DB::table('eleve_sante')->insert([
        'eleve_id' => 1, 'groupe_sanguin' => 'O+', 'allergies_pathologies' => 'Asthme', 'medecin_referent' => 'Dr Mukala',
    ]);

    $eleve = Eleve::find(1);

    $this->actingAs($prefet)->get(route('prefecture.eleves.fiche', $eleve))->assertOk()
        ->assertSee('Aicha')->assertSee('État civil')->assertSee('Dossier médical')
        ->assertSee('Asthme')->assertSee('Parcours scolaire')->assertSee('Assiduité')
        ->assertSee('Transport')
        // La pièce jointe du dossier est visible et ouvrable.
        ->assertSee('Pièces du dossier')->assertSee('Certificat médical')
        ->assertSee('eleves/documents/certificat.pdf', false);
});

test('la recherche d’élève filtre par nom ou matricule', function () {
    $prefet = User::where('role', 'prefetture')->first();
    DB::table('eleves')->insert(['id' => 2, 'matricule' => 'ELV-002', 'nom' => 'Zola', 'prenom' => 'Emile', 'sexe' => 'M', 'statut' => 'actif']);

    $this->actingAs($prefet)->get(route('prefecture.eleves', ['q' => 'Zola']))->assertOk()
        ->assertSee('Emile')->assertDontSee('Aicha');

    $this->actingAs($prefet)->get(route('prefecture.eleves', ['q' => 'ELV-001']))->assertOk()
        ->assertSee('Aicha')->assertDontSee('Emile');
});

test('les pages de vie scolaire sont fermées aux autres rôles', function () {
    $comptable = User::where('role', 'comptable')->first();

    foreach (['prefecture.presences', 'prefecture.horaires', 'prefecture.evaluations', 'prefecture.eleves', 'prefecture.tuteurs'] as $route) {
        $this->actingAs($comptable)->get(route($route))->assertForbidden();
    }

    $this->actingAs($comptable)->get(route('prefecture.eleves.fiche', Eleve::find(1)))->assertForbidden();
});

test('le préfet exerce ses privilèges de supervision', function () {
    $prefet = User::where('role', 'prefetture')->first();
    $eleve = Eleve::find(1);

    // 1. Statut disciplinaire de l'élève (actif / suspendu / exclu).
    $this->actingAs($prefet)->patch(route('prefecture.eleves.statut', $eleve), ['statut' => 'suspendu'])
        ->assertRedirect();
    expect($eleve->fresh()->statut)->toBe('suspendu');

    $this->actingAs($prefet)->patch(route('prefecture.eleves.statut', $eleve), ['statut' => 'exclu'])
        ->assertRedirect();
    expect($eleve->fresh()->statut)->toBe('exclu');

    // 2. Validation d'une évaluation programmée.
    $evaluation = App\Models\Evaluation::find(1);
    $this->actingAs($prefet)->post(route('prefecture.evaluations.valider', $evaluation))->assertRedirect();
    expect($evaluation->fresh()->validee_at)->not->toBeNull()
        ->and((int) $evaluation->fresh()->validee_par)->toBe($prefet->id);

    // Régression : la page doit afficher la date de validation (et non recevoir une chaîne).
    $this->actingAs($prefet)->get(route('prefecture.evaluations'))->assertOk()->assertSee('Validée');

    // 3. Ouverture / fermeture d'une période.
    $periode = App\Models\Periode::find(1);
    $this->actingAs($prefet)->patch(route('prefecture.periodes.toggle', $periode))->assertRedirect();
    expect($periode->fresh()->statut)->toBe('inactif');
    $this->actingAs($prefet)->patch(route('prefecture.periodes.toggle', $periode))->assertRedirect();
    expect($periode->fresh()->statut)->toBe('actif');

    // 4. Approbation d'un bulletin.
    $bulletin = App\Models\Bulletin::create([
        'inscription_id' => 1, 'periode_id' => 1, 'total_points' => 45, 'max_possible' => 50,
        'pourcentage' => 90, 'place_classe' => 1, 'mention' => 'Grande distinction', 'statut' => 'actif',
    ]);
    $this->actingAs($prefet)->post(route('prefecture.bulletins.approuver', $bulletin))->assertRedirect();
    expect($bulletin->fresh()->approuve_at)->not->toBeNull()
        ->and((int) $bulletin->fresh()->approuve_par)->toBe($prefet->id);

    // Régression : la page doit afficher la date d'approbation (et non planter sur une chaîne).
    $this->actingAs($prefet)->get(route('prefecture.bulletins'))->assertOk()->assertSee('Approuvé le');
});

test('les pages périodes et bulletins sont accessibles au préfet', function () {
    $prefet = User::where('role', 'prefetture')->first();

    $this->actingAs($prefet)->get(route('prefecture.periodes'))->assertOk()
        ->assertSee('1er trimestre')->assertSee('Ouverte')->assertSee('Fermer');

    App\Models\Bulletin::create([
        'inscription_id' => 1, 'periode_id' => 1, 'total_points' => 45, 'max_possible' => 50,
        'pourcentage' => 90, 'place_classe' => 1, 'mention' => 'Grande distinction', 'statut' => 'actif',
    ]);

    $this->actingAs($prefet)->get(route('prefecture.bulletins'))->assertOk()
        ->assertSee('Aicha')->assertSee('Grande distinction')->assertSee('Approuver');
});

 test('le préfet contrôle l’emploi du temps (consultation, création)', function () {
     $prefet = User::where('role', 'prefetture')->first();

     $this->actingAs($prefet)->get(route('admin.emplois-du-temps.index'))
         ->assertOk()
         ->assertSee('Mathématiques');

     $this->actingAs($prefet)->post(route('admin.emplois-du-temps.store'), [
         'affectation_id' => 1,
         'jour' => 'mardi',
         'heure_debut' => '10:00',
         'heure_fin' => '11:00',
         'salle_id' => 1,
     ])->assertRedirect(route('admin.emplois-du-temps.index'));

     expect(DB::table('emplois_du_temps')->where('jour', 'mardi')->count())->toBe(1);
 });

 test('le préfet modifie le dossier d’un élève', function () {
     $prefet = User::where('role', 'prefetture')->first();
     $eleve = Eleve::find(1);

     $this->actingAs($prefet)->get(route('admin.eleves.edit', $eleve))->assertOk();

     $this->actingAs($prefet)->put(route('admin.eleves.update', $eleve), [
         'nom' => 'Banzolo', 'postnom' => 'Nsimba', 'prenom' => 'Aicha', 'sexe' => 'F',
         'date_naissance' => '2017-05-12', 'lieu_naissance' => 'Kinshasa', 'statut' => 'actif',
         'nom_mere' => 'Mama', 'telephone_mere' => '0990000000',
     ])->assertRedirect();

     expect(Eleve::find(1)->postnom)->toBe('Nsimba');
 });

 test('un professeur ne peut ni piloter les horaires ni modifier un dossier élève', function () {
     $professeur = User::factory()->create(['role' => 'professeur']);
     $eleve = Eleve::find(1);

     $this->actingAs($professeur)->get(route('admin.emplois-du-temps.index'))->assertForbidden();
     $this->actingAs($professeur)->get(route('admin.eleves.edit', $eleve))->assertForbidden();
 });

  test('le préfet contrôle les affectations et mutations des professeurs', function () {
      $prefet = User::where('role', 'prefetture')->first();

      $this->actingAs($prefet)->get(route('admin.affectations.index'))
          ->assertOk()
          ->assertSee('Mathématiques');

      // Mutation : on réaffecte la classe de l'affectation existante.
      DB::table('classes')->insert(['id' => 2, 'nom' => '4e primaire', 'cycle_id' => 1, 'salle_id' => 1, 'statut' => 'actif']);

      $this->actingAs($prefet)->patch(route('admin.affectations.muter', 1), [
          'professeur_id' => 1,
          'cours_id' => 1,
          'classe_id' => 2,
      ])->assertRedirect();

      expect((int) DB::table('affectations_cours')->where('id', 1)->value('classe_id'))->toBe(2);
  });

  test('un professeur n’a pas accès au module d’affectations', function () {
      $professeur = User::factory()->create(['role' => 'professeur']);

      $this->actingAs($professeur)->get(route('admin.affectations.index'))->assertForbidden();
  });
