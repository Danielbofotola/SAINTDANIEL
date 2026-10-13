<?php

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Deliberation;
use App\Models\Eleve;
use App\Models\Inscription;
use App\Models\ParametreSysteme;
use App\Models\User;
use App\Support\Jury;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'classe_destinations', 'deliberations', 'notes', 'evaluations', 'periodes', 'affectations_cours', 'cours',
        'inscriptions', 'eleves', 'classes', 'cycles', 'salles', 'annees_scolaires',
        'sections', 'options',
        'notifications', 'professeurs', 'parametres_systeme', 'users',
         'eleve_user',
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
        $table->string('section_id')->nullable();
        $table->string('option_id')->nullable();
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
 * Scénario : deux classes (ordre 1 et 2), une branche notée sur 20+20, quatre
 * élèves dont les moyennes tombent dans chaque cas de figure.
 *
 * @return array{alice: User, classe1: Classe, classe2: Classe, annee: AnneeScolaire, cible: AnneeScolaire, eleves: array<string, Eleve>}
 */
function scenarioDeliberation(): array
{
    $admin = User::factory()->create(['role' => 'admin', 'nom' => 'Mwamba', 'prenom' => 'Alice']);

    $annee = AnneeScolaire::create(['libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-15', 'is_encours' => true, 'statut' => 'actif']);
    $cible = AnneeScolaire::create(['libelle' => '2027-2028', 'date_debut' => '2027-09-01', 'date_fin' => '2028-07-15', 'is_encours' => false, 'statut' => 'actif']);

    $cycle = DB::table('cycles')->insertGetId(['nom' => 'Primaire', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);

    $classe1 = Classe::create(['cycle_id' => $cycle, 'nom' => '3e primaire', 'ordre' => 1, 'statut' => 'actif']);
    $classe2 = Classe::create(['cycle_id' => $cycle, 'nom' => '4e primaire', 'ordre' => 2, 'statut' => 'actif']);

    $cours = DB::table('cours')->insertGetId([
        'code' => 'MATH', 'intitule' => 'Mathématiques', 'ponderation' => 1,
        'max_travaux_journaliers' => 20, 'max_examen' => 20, 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $affectation = DB::table('affectations_cours')->insertGetId([
        'cours_id' => $cours, 'classe_id' => $classe1->id, 'annee_scolaire_id' => $annee->id,
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

    // 18/20 + 18/20 = 90 % · 10/20 + 10/20 = 50 % · 9,5 + 9,5 = 47,5 % · 8 + 8 = 40 %
    $plans = ['A' => 18.0, 'B' => 10.0, 'C' => 9.5, 'D' => 8.0];
    $eleves = [];

    foreach ($plans as $cle => $note) {
        $eleve = Eleve::create([
            'matricule' => 'ELV-'.str_pad($cle, 3, '0', STR_PAD_LEFT),
            'nom' => 'Nom'.$cle, 'prenom' => 'Prenom'.$cle, 'sexe' => 'M', 'statut' => 'actif',
        ]);
        Inscription::create([
            'eleve_id' => $eleve->id, 'classe_id' => $classe1->id, 'annee_scolaire_id' => $annee->id,
            'date_inscription' => '2026-09-05', 'statut' => Inscription::STATUT_ACTIF,
        ]);
        foreach ([$interro, $examen] as $evaluation) {
            DB::table('notes')->insert([
                'evaluation_id' => $evaluation, 'eleve_id' => $eleve->id, 'note_obtenue' => $note,
                'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $eleves[$cle] = $eleve;
    }

    return ['alice' => $admin, 'classe1' => $classe1, 'classe2' => $classe2, 'annee' => $annee, 'cible' => $cible, 'eleves' => $eleves];
}

test('le jury décide selon les seuils, en réutilisant la moyenne du bulletin', function () {
    $s = scenarioDeliberation();
    $jury = Jury::regles();

    $analyse = fn (Eleve $eleve) => $jury->analyser(
        Inscription::where('eleve_id', $eleve->id)->firstOrFail()
    );

    expect($analyse($s['eleves']['A'])['pourcentage'])->toBe(90.0)
        ->and($analyse($s['eleves']['A'])['decision_proposee'])->toBe('admis')
        ->and($analyse($s['eleves']['B'])['pourcentage'])->toBe(50.0)
        ->and($analyse($s['eleves']['B'])['decision_proposee'])->toBe('admis')
        ->and($analyse($s['eleves']['C'])['pourcentage'])->toBe(47.5)
        ->and($analyse($s['eleves']['C'])['decision_proposee'])->toBe('ajourne')
        ->and($analyse($s['eleves']['D'])['pourcentage'])->toBe(40.0)
        ->and($analyse($s['eleves']['D'])['decision_proposee'])->toBe('redouble');
});

test('un plafond de cours en échec bloque l’admission', function () {
    $s = scenarioDeliberation();
    ParametreSysteme::current()->update(['deliberation_max_cours_echoues' => 0]);

    // L'élève A a 90 % mais sa branche unique est… réussie : il reste admis.
    $analyseA = Jury::regles()->analyser(Inscription::where('eleve_id', $s['eleves']['A']->id)->firstOrFail());
    expect($analyseA['decision_proposee'])->toBe('admis');

    // Avec un plafond de 0 cours en échec, un élève à 50 % avec une branche
    // sous le seuil doit être bloqué : on abaisse sa note d'examen.
    $eleveB = $s['eleves']['B'];
    DB::table('notes')->where('eleve_id', $eleveB->id)->update(['note_obtenue' => 4]);
    $analyse = Jury::regles()->analyser(Inscription::where('eleve_id', $eleveB->id)->firstOrFail());
    expect($analyse['cours_echoues'])->toBe(1)
        ->and($analyse['decision_proposee'])->not->toBe('admis');
});

test('l’écran de délibération liste les élèves et leurs propositions', function () {
    $s = scenarioDeliberation();

    $this->actingAs($s['alice'])
        ->get(route('admin.deliberations.index'))
        ->assertOk()
        ->assertSee('Délibération')
        ->assertSee('3e primaire')
        ->assertSee('4e primaire')       // classe suivante annoncée
        ->assertSee('NomA')
        ->assertSee('Admis')
        // Une classe qui n'est pas une fin de parcours n'offre pas « Diplômé ».
        ->assertDontSee('Diplômé');
});

test('l’enregistrement des décisions est persisté et marqué manuel si forcé', function () {
    $s = scenarioDeliberation();
    $inscriptionA = Inscription::where('eleve_id', $s['eleves']['A']->id)->firstOrFail();
    $inscriptionD = Inscription::where('eleve_id', $s['eleves']['D']->id)->firstOrFail();

    $this->actingAs($s['alice'])
        ->post(route('admin.deliberations.enregistrer'), [
            'annee_scolaire_id' => $s['annee']->id,
            'decisions' => [
                $inscriptionA->id => 'admis',
                $inscriptionD->id => 'ajourne',   // forcé : la proposition était « redouble »
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('success', fn ($m) => str_contains($m, '2 décision'));

    $a = Deliberation::where('inscription_id', $inscriptionA->id)->firstOrFail();
    $d = Deliberation::where('inscription_id', $inscriptionD->id)->firstOrFail();

    expect($a->decision)->toBe('admis')
        ->and((float) $a->pourcentage_general)->toBe(90.0)
        ->and($a->manuelle)->toBeFalse()
        ->and($a->classe_suivante_id)->toBe($s['classe2']->id)
        ->and($d->decision)->toBe('ajourne')
        ->and($d->manuelle)->toBeTrue();       // décision forcée à la main
});

test('la décision de fin d’année est notifiée aux comptes parents', function () {
    $s = scenarioDeliberation();
    $inscriptionA = Inscription::where('eleve_id', $s['eleves']['A']->id)->firstOrFail();

    $parent = User::factory()->create(['role' => 'parent', 'nom' => 'Mukendi', 'prenom' => 'Sarah']);
    $s['eleves']['A']->parents()->sync([$parent->id]);

    $this->actingAs($s['alice'])
        ->post(route('admin.deliberations.enregistrer'), [
            'annee_scolaire_id' => $s['annee']->id,
            'decisions' => [$inscriptionA->id => 'admis'],
        ])
        ->assertRedirect()
        ->assertSessionHas('success', fn ($m) => str_contains($m, '1 notification'));

    $notification = $parent->fresh()->notifications()->latest()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['decision'])->toBe('admis')
        ->and($notification->data['niveau'])->toBe('success')
        ->and($notification->data['titre'])->toContain("fin d'année")
        ->and($notification->data['message'])->toContain('classe supérieure')
        ->and($notification->data['lien'])->toContain('/espace-parent/enfants');
});

test('l’application en masse promeut les admis et maintient les autres', function () {
    $s = scenarioDeliberation();

    $decisions = [];
    foreach (['A' => 'admis', 'B' => 'admis', 'C' => 'ajourne', 'D' => 'redouble'] as $cle => $decision) {
        $decisions[Inscription::where('eleve_id', $s['eleves'][$cle]->id)->value('id')] = $decision;
    }

    $this->actingAs($s['alice'])->post(route('admin.deliberations.enregistrer'), [
        'annee_scolaire_id' => $s['annee']->id, 'decisions' => $decisions,
    ])->assertRedirect();

    $this->actingAs($s['alice'])->post(route('admin.deliberations.appliquer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'annee_cible_id' => $s['cible']->id,
    ])->assertRedirect()->assertSessionHas('success', fn ($m) => str_contains($m, '2 élève(s) promu(s)'));

    $classeDans = fn (string $cle) => Inscription::where('eleve_id', $s['eleves'][$cle]->id)
        ->where('annee_scolaire_id', $s['cible']->id)->first();

    // Admis → classe d'ordre supérieur, en attente de validation par la caisse.
    expect($classeDans('A'))->not->toBeNull()
        ->and($classeDans('A')->classe_id)->toBe($s['classe2']->id)
        ->and($classeDans('A')->statut)->toBe(Inscription::STATUT_EN_ATTENTE)
        ->and($classeDans('B')->classe_id)->toBe($s['classe2']->id)
        // Ajourné / redoublant → même classe.
        ->and($classeDans('C')->classe_id)->toBe($s['classe1']->id)
        ->and($classeDans('D')->classe_id)->toBe($s['classe1']->id);

    // Deuxième application : rien n'est dupliqué.
    $this->actingAs($s['alice'])->post(route('admin.deliberations.appliquer'), [
        'annee_scolaire_id' => $s['annee']->id, 'annee_cible_id' => $s['cible']->id,
    ])->assertSessionHas('success', fn ($m) => str_contains($m, '4 ignoré(s)'));

    expect(Inscription::where('annee_scolaire_id', $s['cible']->id)->count())->toBe(4);
});

test('la délibération est réservée à l’administration', function () {
    $s = scenarioDeliberation();
    $prof = User::factory()->create(['role' => 'professeur']);

    $this->actingAs($prof)->get(route('admin.deliberations.index'))->assertForbidden();
    $this->actingAs($prof)->post(route('admin.deliberations.appliquer'), [])->assertForbidden();
});

test('les critères sont enregistrés et le repêchage ne peut dépasser l’admission', function () {
    $s = scenarioDeliberation();

    $this->actingAs($s['alice'])->put(route('admin.deliberations.criteres'), [
        'deliberation_seuil_admission' => 55, 'deliberation_seuil_repechage' => 45,
        'deliberation_max_cours_echoues' => 1, 'deliberation_active' => 1,
    ])->assertRedirect()->assertSessionHas('success');

    $regles = ParametreSysteme::current();
    expect((float) $regles->deliberation_seuil_admission)->toBe(55.0)
        ->and($regles->deliberation_max_cours_echoues)->toBe(1)
        ->and($regles->deliberation_active)->toBeTrue();

    // Repêchage au-dessus de l'admission : refusé.
    $this->actingAs($s['alice'])->put(route('admin.deliberations.criteres'), [
        'deliberation_seuil_admission' => 40, 'deliberation_seuil_repechage' => 70,
    ])->assertSessionHas('error');

    expect((float) ParametreSysteme::current()->deliberation_seuil_admission)->toBe(55.0);
});

test('le formulaire de classe expose le niveau et la progression s’en déduit', function () {
    $s = scenarioDeliberation();

    // Le champ « niveau » est bien présent sur le formulaire de classe.
    $this->actingAs($s['alice'])
        ->get(route('admin.classes.create'))
        ->assertOk()
        ->assertSee('name="ordre"', false)
        ->assertSee('Niveau');

    // Il est enregistré, et la classe suivante se déduit du niveau.
    $this->actingAs($s['alice'])->post(route('admin.classes.store'), [
        'cycle_id' => $s['classe1']->cycle_id,
        'nom' => '5e primaire',
        'ordre' => 3,
        'statut' => 'actif',
    ])->assertRedirect(route('admin.classes.index'));

    $nouvelle = Classe::where('nom', '5e primaire')->firstOrFail();

    expect($nouvelle->ordre)->toBe(3)
        ->and($s['classe2']->suivante()?->id)->toBe($nouvelle->id)
        ->and($nouvelle->suivante())->toBeNull();   // plus rien au-dessus
});

test('un passage explicite franchit les cycles et prime sur le niveau', function () {
    $s = scenarioDeliberation();

    // Autre cycle : aucune classe n'y a l'ordre 3, le passage ne pouvait pas être déduit.
    $secondaire = DB::table('cycles')->insertGetId([
        'nom' => 'Secondaire', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $classeSec = Classe::create(['cycle_id' => $secondaire, 'nom' => '1re secondaire', 'ordre' => 1, 'statut' => 'actif']);

    $s['classe2']->update(['classe_suivante_id' => $classeSec->id]);

    expect($s['classe2']->fresh()->suivante()?->id)->toBe($classeSec->id)
        ->and($classeSec->suivante())->toBeNull();

    // Le passage explicite reste prioritaire sur l'ordre immédiatement supérieur.
    $s['classe1']->update(['classe_suivante_id' => $classeSec->id]);
    expect($s['classe1']->fresh()->suivante()?->id)->toBe($classeSec->id);
});

test('le formulaire de classe fixe la classe suivante sans boucle sur elle-même', function () {
    $s = scenarioDeliberation();

    $this->actingAs($s['alice'])
        ->get(route('admin.classes.create'))
        ->assertOk()
        ->assertSee('name="classe_suivante_id"', false);

    $this->actingAs($s['alice'])->post(route('admin.classes.store'), [
        'cycle_id' => $s['classe1']->cycle_id,
        'nom' => 'Classe test',
        'classe_suivante_id' => $s['classe2']->id,
        'statut' => 'actif',
    ])->assertRedirect(route('admin.classes.index'));

    $nouvelle = Classe::where('nom', 'Classe test')->firstOrFail();
    expect($nouvelle->classe_suivante_id)->toBe($s['classe2']->id);

    // Une classe ne peut pas être sa propre suivante.
    $this->actingAs($s['alice'])->put(route('admin.classes.update', $nouvelle), [
        'cycle_id' => $nouvelle->cycle_id,
        'nom' => $nouvelle->nom,
        'classe_suivante_id' => $nouvelle->id,
        'statut' => 'actif',
    ])->assertSessionHasErrors('classe_suivante_id');
});

test('l’année suivante est déduite de l’année en cours et proposée au passage', function () {
    $s = scenarioDeliberation();

    expect(AnneeScolaire::suivante($s['annee'])?->id)->toBe($s['cible']->id)
        ->and(AnneeScolaire::suivante($s['cible']))->toBeNull();

    $this->actingAs($s['alice'])
        ->get(route('admin.deliberations.index'))
        ->assertOk()
        ->assertSee('année suivante');
});

test('le passage refuse une année cible antérieure à l’année délibérée', function () {
    $s = scenarioDeliberation();
    $ancienne = AnneeScolaire::create([
        'libelle' => '2024-2025', 'date_debut' => '2024-09-01', 'date_fin' => '2025-07-15', 'statut' => 'actif',
    ]);

    $this->actingAs($s['alice'])->post(route('admin.deliberations.appliquer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'annee_cible_id' => $ancienne->id,
    ])->assertSessionHas('error');

    expect(Inscription::where('annee_scolaire_id', $ancienne->id)->count())->toBe(0);
});

test('la liste des élèves d’une année montre le passage et les nouveaux inscrits', function () {
    $s = scenarioDeliberation();

    // Délibérer puis appliquer le passage : A est promu dans la classe cible.
    $this->actingAs($s['alice'])->post(route('admin.deliberations.enregistrer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'decisions' => [Inscription::where('eleve_id', $s['eleves']['A']->id)->value('id') => 'admis'],
    ])->assertRedirect();

    $this->actingAs($s['alice'])->post(route('admin.deliberations.appliquer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'annee_cible_id' => $s['cible']->id,
    ])->assertRedirect();

    // Un nouvel inscrit, sans délibération d'origine.
    $nouveau = Eleve::create(['matricule' => 'ELV-NEW', 'nom' => 'Nouveau', 'prenom' => 'Eleve', 'sexe' => 'M', 'statut' => 'actif']);
    Inscription::create([
        'eleve_id' => $nouveau->id, 'classe_id' => $s['classe2']->id, 'annee_scolaire_id' => $s['cible']->id,
        'date_inscription' => '2027-09-05', 'statut' => Inscription::STATUT_EN_ATTENTE,
    ]);

    $this->actingAs($s['alice'])
        ->get(route('admin.eleves.index', ['annee' => $s['cible']->id]))
        ->assertOk()
        ->assertSee('2027-2028')
        ->assertSee('NomA')          // l'élève promu par la délibération
        ->assertSee('Passage')       // sa provenance
        ->assertSee('Nouveau')       // le nouvel inscrit est marqué « Nouveau »
        ->assertSee('ELV-NEW');      // …et bien présent dans la liste

    // Sans la délibération d'origine, le nouvel inscrit est marqué « Nouveau ».
    expect(Inscription::where('annee_scolaire_id', $s['cible']->id)->count())->toBe(2);
});

test('la synthèse de délibération compte admis, ajournés, redoublants et le sexe', function () {
    $s = scenarioDeliberation();
    $s['eleves']['B']->update(['sexe' => 'F']);   // A (M) et B (F) sont admis

    $this->actingAs($s['alice'])
        ->get(route('admin.deliberations.index'))
        ->assertOk()
        ->assertSee('Synthèse')
        ->assertSee('Admis (passent)')
        ->assertSee('Redoublants')
        ->assertSee('1 G · 1 F');   // admis : 1 garçon (A) + 1 fille (B)
});
