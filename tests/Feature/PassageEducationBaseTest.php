<?php

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Deliberation;
use App\Models\Eleve;
use App\Models\Inscription;
use App\Models\Option;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Passage éducation de base → humanités : la classe terminale du cycle de base
 * mène vers plusieurs classes d'accueil (une par section/option). La délibération
 * doit alors recueillir le choix de chaque élève admis avant d'appliquer le passage.
 */
beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'classe_destinations', 'deliberations', 'notes', 'evaluations', 'periodes',
        'affectations_cours', 'cours', 'inscriptions', 'eleves', 'classes', 'cycles',
        'salles', 'annees_scolaires', 'sections', 'options',
        'notifications', 'professeurs', 'parametres_systeme', 'users', 'eleve_user',
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
        $table->string('photo')->nullable();
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
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('parametres_systeme', function (Blueprint $table) {
        $table->id();
        $table->string('nom_etablissement', 150)->default('Etablissement');
        $table->string('nom_court', 60)->default('Ecole');
        $table->boolean('saisie_notes_active')->default(true);
        $table->boolean('portail_parents_actif')->default(false);
        $table->boolean('deliberation_active')->default(false);
        $table->decimal('deliberation_seuil_admission', 5, 2)->default(50);
        $table->decimal('deliberation_seuil_repechage', 5, 2)->default(45);
        $table->unsignedInteger('deliberation_max_cours_echoues')->nullable();
        $table->decimal('deliberation_note_min_cours', 5, 2)->nullable();
        $table->timestamps();
    });
    DB::table('parametres_systeme')->insert(['nom_court' => 'Ecole Test', 'created_at' => now(), 'updated_at' => now()]);

    Schema::create('annees_scolaires', function (Blueprint $table) {
        $table->id();
        $table->string('libelle', 20)->unique();
        $table->date('date_debut');
        $table->date('date_fin');
        $table->boolean('is_encours')->default(false);
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('cycles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->unsignedInteger('ordre')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('salles', function (Blueprint $table) {
        $table->id();
        $table->string('nom')->nullable();
        $table->timestamps();
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
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('salle_id')->nullable();
        $table->string('nom');
        $table->unsignedInteger('ordre')->nullable();
        $table->unsignedBigInteger('classe_suivante_id')->nullable();
        $table->unsignedBigInteger('section_id')->nullable();
        $table->unsignedBigInteger('option_id')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('classe_destinations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('destination_id');
        $table->timestamps();
        $table->unique(['classe_id', 'destination_id']);
    });
    Schema::create('eleves', function (Blueprint $table) {
        $table->id();
        $table->string('matricule')->unique();
        $table->string('nom');
        $table->string('postnom')->nullable();
        $table->string('prenom');
        $table->string('sexe')->nullable();
        $table->date('date_naissance')->nullable();
        $table->unsignedBigInteger('tuteur_id')->nullable();
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
    Schema::create('cours', function (Blueprint $table) {
        $table->id();
        $table->string('code');
        $table->string('intitule');
        $table->unsignedInteger('ponderation')->default(1);
        $table->unsignedInteger('max_travaux_journaliers')->nullable();
        $table->unsignedInteger('max_examen')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('affectations_cours', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable();
        $table->unsignedBigInteger('cours_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
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
        $table->unsignedBigInteger('periode_id');
        $table->string('titre');
        $table->string('type_evaluation')->default('interro');
        $table->decimal('max_note', 5, 2)->default(20);
        $table->date('date_evaluation')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('notes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('evaluation_id');
        $table->unsignedBigInteger('eleve_id');
        $table->decimal('note_obtenue', 5, 2)->nullable();
        $table->string('observation')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('deliberations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('inscription_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->decimal('pourcentage_general', 5, 2)->default(0);
        $table->unsignedInteger('cours_echoues')->default(0);
        $table->string('decision', 20)->default('redouble');
        $table->text('critere')->nullable();
        $table->boolean('manuelle')->default(false);
        $table->unsignedBigInteger('classe_suivante_id')->nullable();
        $table->unsignedBigInteger('decide_par')->nullable();
        $table->timestamp('decide_le')->nullable();
        $table->text('commentaire')->nullable();
        $table->timestamps();
        $table->unique(['inscription_id', 'annee_scolaire_id']);
    });
    Schema::create('eleve_user', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('user_id');
        $table->timestamps();
    });
    Schema::create('notifications', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('type');
        $table->morphs('notifiable');
        $table->text('data');
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
    });
});

/**
 * Scénario : fin de l'éducation de base → 1res humanités (Scientifique ou Littéraire).
 *
 * @return array{admin: User, base: Classe, humanites: array<string, Classe>, sections: array<string, Section>, annee: AnneeScolaire, cible: AnneeScolaire, inscription: Inscription, eleve: Eleve}
 */
function scenarioPassageBase(): array
{
    $admin = User::factory()->create(['role' => 'admin', 'nom' => 'Mwamba', 'prenom' => 'Alice']);

    $annee = AnneeScolaire::create(['libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-15', 'is_encours' => true, 'statut' => 'actif']);
    $cible = AnneeScolaire::create(['libelle' => '2027-2028', 'date_debut' => '2027-09-01', 'date_fin' => '2028-07-15', 'is_encours' => false, 'statut' => 'actif']);

    $cycleBase = DB::table('cycles')->insertGetId(['nom' => 'Éducation de base', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $cycleHum = DB::table('cycles')->insertGetId(['nom' => 'Humanités', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);

    $base = Classe::create(['cycle_id' => $cycleBase, 'nom' => '8e année de base', 'ordre' => 8, 'statut' => 'actif']);

    $sectionSci = Section::create(['nom' => 'Scientifique']);
    $sectionLit = Section::create(['nom' => 'Littéraire']);
    $optMath = Option::create(['section_id' => $sectionSci->id, 'nom' => 'Mathématiques - Physique']);
    $optLatin = Option::create(['section_id' => $sectionLit->id, 'nom' => 'Latin - Philosophie']);

    $hSci = Classe::create(['cycle_id' => $cycleHum, 'nom' => '1re humanité Scientifique', 'ordre' => 1, 'section_id' => $sectionSci->id, 'option_id' => $optMath->id, 'statut' => 'actif']);
    $hLit = Classe::create(['cycle_id' => $cycleHum, 'nom' => '1re humanité Littéraire', 'ordre' => 1, 'section_id' => $sectionLit->id, 'option_id' => $optLatin->id, 'statut' => 'actif']);

    // La classe terminale de l'éducation de base mène vers les deux humanités.
    $base->destinations()->sync([$hSci->id, $hLit->id]);

    $cours = DB::table('cours')->insertGetId([
        'code' => 'MATH', 'intitule' => 'Mathématiques', 'ponderation' => 1,
        'max_travaux_journaliers' => 20, 'max_examen' => 20, 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $affectation = DB::table('affectations_cours')->insertGetId([
        'cours_id' => $cours, 'classe_id' => $base->id, 'annee_scolaire_id' => $annee->id,
        'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $periode = DB::table('periodes')->insertGetId([
        'annee_scolaire_id' => $annee->id, 'nom' => '1er trimestre', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $interro = DB::table('evaluations')->insertGetId([
        'affectation_id' => $affectation, 'periode_id' => $periode, 'titre' => 'Interro 1',
        'type_evaluation' => 'interro', 'max_note' => 20, 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $examen = DB::table('evaluations')->insertGetId([
        'affectation_id' => $affectation, 'periode_id' => $periode, 'titre' => 'Examen',
        'type_evaluation' => 'examen', 'max_note' => 20, 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $eleve = Eleve::create(['matricule' => 'ELV-001', 'nom' => 'NomA', 'prenom' => 'PrenomA', 'sexe' => 'M', 'statut' => 'actif']);
    $inscription = Inscription::create([
        'eleve_id' => $eleve->id, 'classe_id' => $base->id, 'annee_scolaire_id' => $annee->id,
        'date_inscription' => '2026-09-05', 'statut' => Inscription::STATUT_ACTIF,
    ]);

    foreach ([$interro, $examen] as $evaluation) {
        DB::table('notes')->insert([
            'evaluation_id' => $evaluation, 'eleve_id' => $eleve->id, 'note_obtenue' => 18.0,
            'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    return [
        'admin' => $admin,
        'base' => $base,
        'humanites' => ['sci' => $hSci, 'lit' => $hLit],
        'sections' => ['sci' => $sectionSci, 'lit' => $sectionLit],
        'annee' => $annee,
        'cible' => $cible,
        'inscription' => $inscription,
        'eleve' => $eleve,
    ];
}

test('une classe terminale sans section expose plusieurs candidats', function () {
    $s = scenarioPassageBase();

    expect($s['base']->passageAmbigu())->toBeTrue()
        ->and($s['base']->candidats()->pluck('id')->all())
        ->toEqualCanonicalizing([$s['humanites']['sci']->id, $s['humanites']['lit']->id]);
});

test('l’écran de délibération propose le choix section/option quand le passage est multiple', function () {
    $s = scenarioPassageBase();

    $this->actingAs($s['admin'])
        ->get(route('admin.deliberations.index', ['classe' => $s['base']->id]))
        ->assertOk()
        ->assertSee('Passage à choix')
        ->assertSee('name="destinations['.$s['inscription']->id.']"', false)
        ->assertSee('Mathématiques - Physique')
        ->assertSee('Latin - Philosophie');
});

test('le choix de la section/option est mémorisé sur la décision de l’élève', function () {
    $s = scenarioPassageBase();

    $this->actingAs($s['admin'])->post(route('admin.deliberations.enregistrer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'decisions' => [$s['inscription']->id => 'admis'],
        'destinations' => [$s['inscription']->id => $s['humanites']['lit']->id],
    ])->assertRedirect()->assertSessionHas('success');

    $decision = Deliberation::where('inscription_id', $s['inscription']->id)->firstOrFail();

    expect($decision->decision)->toBe('admis')
        ->and($decision->classe_suivante_id)->toBe($s['humanites']['lit']->id);
});

test('le passage inscrit l’élève admis dans la section/option choisie', function () {
    $s = scenarioPassageBase();

    $this->actingAs($s['admin'])->post(route('admin.deliberations.enregistrer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'decisions' => [$s['inscription']->id => 'admis'],
        'destinations' => [$s['inscription']->id => $s['humanites']['sci']->id],
    ])->assertRedirect();

    $this->actingAs($s['admin'])->post(route('admin.deliberations.appliquer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'annee_cible_id' => $s['cible']->id,
    ])->assertRedirect()->assertSessionHas('success', fn ($m) => str_contains($m, '1 élève(s) promu(s)'));

    $cree = Inscription::where('eleve_id', $s['eleve']->id)
        ->where('annee_scolaire_id', $s['cible']->id)
        ->firstOrFail();

    expect($cree->classe_id)->toBe($s['humanites']['sci']->id)
        ->and($cree->statut)->toBe(Inscription::STATUT_EN_ATTENTE);
});

test('sans choix de section/option, l’admis ambigu n’est pas promu et l’administrateur est averti', function () {
    $s = scenarioPassageBase();

    $this->actingAs($s['admin'])->post(route('admin.deliberations.enregistrer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'decisions' => [$s['inscription']->id => 'admis'],
    ])->assertRedirect();

    $this->actingAs($s['admin'])->post(route('admin.deliberations.appliquer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'annee_cible_id' => $s['cible']->id,
    ])->assertRedirect()->assertSessionHas('error', fn ($m) => str_contains($m, 'sans section/option'));

    expect(Inscription::where('annee_scolaire_id', $s['cible']->id)->count())->toBe(0);
});

test('le formulaire de classe enregistre les destinations multiples', function () {
    $s = scenarioPassageBase();

    $this->actingAs($s['admin'])->post(route('admin.classes.store'), [
        'cycle_id' => $s['base']->cycle_id,
        'nom' => 'Nouvelle terminale',
        'ordre' => 8,
        'statut' => 'actif',
        'destinations' => [$s['humanites']['sci']->id, $s['humanites']['lit']->id],
    ])->assertRedirect(route('admin.classes.index'));

    $terminale = Classe::where('nom', 'Nouvelle terminale')->firstOrFail();

    expect($terminale->destinations()->pluck('classes.id')->all())
        ->toEqualCanonicalizing([$s['humanites']['sci']->id, $s['humanites']['lit']->id]);
});
