<?php

use App\Models\Inscription;
use App\Models\ParametreSysteme;
use App\Models\User;
use App\Support\BulletinRdc;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'bulletins', 'notes', 'evaluations', 'periodes', 'affectations_cours', 'cours',
        'professeurs', 'inscriptions', 'eleves', 'tuteurs', 'classes', 'cycles', 'salles',
        'annees_scolaires', 'parametres_systeme', 'users',
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
    Schema::create('parametres_systeme', function (Blueprint $table) {
        $table->id();
        $table->string('nom_etablissement', 150)->nullable();
        $table->string('nom_court', 60)->nullable();
        $table->string('telephone', 30)->nullable();
        $table->string('email', 150)->nullable();
        $table->text('adresse')->nullable();
        $table->boolean('saisie_notes_active')->default(true);
        $table->boolean('portail_parents_actif')->default(false);
        $table->boolean('deliberation_active')->default(false);
        $table->decimal('deliberation_seuil_admission', 5, 2)->default(50);
        $table->decimal('deliberation_seuil_repechage', 5, 2)->default(45);
        $table->unsignedInteger('deliberation_max_cours_echoues')->nullable();
        $table->decimal('deliberation_note_min_cours', 5, 2)->nullable();
        $table->string('bulletin_pays', 100)->nullable();
        $table->string('bulletin_ministere', 160)->nullable();
        $table->string('bulletin_titre', 160)->nullable();
        $table->string('bulletin_reference', 50)->nullable();
        $table->string('bulletin_province', 100)->nullable();
        $table->string('bulletin_ville', 100)->nullable();
        $table->string('bulletin_commune', 100)->nullable();
        $table->string('bulletin_code_ecole', 50)->nullable();
        $table->string('bulletin_lieu_signature', 100)->nullable();
        $table->string('bulletin_signataire', 120)->nullable();
        $table->decimal('bulletin_seuil_reussite', 5, 2)->default(50);
        $table->boolean('bulletin_afficher_place')->default(true);
        $table->boolean('bulletin_afficher_conduite')->default(true);
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
    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('statut')->default('actif');
    });
    Schema::create('cours', function (Blueprint $table) {
        $table->id();
        $table->string('code', 20)->unique();
        $table->string('intitule', 100);
        $table->unsignedInteger('ponderation')->default(1);
        $table->unsignedInteger('max_travaux_journaliers')->nullable();
        $table->unsignedInteger('max_examen')->nullable();
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
    Schema::create('tuteurs', function (Blueprint $table) {
        $table->id();
        $table->string('nom_mere');
        $table->string('telephone_mere');
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
    Schema::create('evaluations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('affectation_id');
        $table->unsignedBigInteger('periode_id');
        $table->string('titre', 100);
        $table->string('type_evaluation')->default('interro');
        $table->decimal('max_note', 5, 2)->default(20);
        $table->date('date_evaluation');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('notes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('evaluation_id');
        $table->unsignedBigInteger('eleve_id');
        $table->decimal('note_obtenue', 5, 2);
        $table->string('observation')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('bulletins', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('inscription_id');
        $table->unsignedBigInteger('periode_id');
        $table->decimal('total_points', 7, 2)->default(0);
        $table->decimal('max_possible', 7, 2)->default(0);
        $table->decimal('pourcentage', 5, 2)->default(0);
        $table->unsignedInteger('place_classe')->nullable();
        $table->string('mention', 50)->nullable();
        $table->string('application', 50)->nullable();
        $table->string('conduite', 50)->nullable();
        $table->string('chemin_pdf')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif',
    ]);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'nom' => '8e CTEB', 'statut' => 'actif']);
    DB::table('professeurs')->insert(['id' => 1, 'statut' => 'actif']);
    DB::table('cours')->insert(['id' => 1, 'code' => 'ALG', 'intitule' => 'Algèbre', 'ponderation' => 4, 'statut' => 'actif']);
    DB::table('affectations_cours')->insert([
        'id' => 1, 'professeur_id' => 1, 'cours_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'statut' => 'actif',
    ]);
    DB::table('periodes')->insert(['id' => 1, 'annee_scolaire_id' => 1, 'nom' => '1ère Période', 'statut' => 'actif']);
});

function seedBulletinEleve(): int
{
    $tuteurId = DB::table('tuteurs')->insertGetId(['nom_mere' => 'Marie', 'telephone_mere' => '0990', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $eleveId = DB::table('eleves')->insertGetId([
        'matricule' => 'ELV-2026-0001', 'nom' => 'Mwamba', 'postnom' => 'Kabila', 'prenom' => 'Aline',
        'sexe' => 'F', 'date_naissance' => '2012-05-12', 'lieu_naissance' => 'Kinshasa',
        'tuteur_id' => $tuteurId, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);

    return DB::table('inscriptions')->insertGetId([
        'eleve_id' => $eleveId, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-05',
        'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

function seedNote(int $eleveId, string $type, float $max, float $obtenu): void
{
    $evaluationId = DB::table('evaluations')->insertGetId([
        'affectation_id' => 1, 'periode_id' => 1, 'titre' => $type.' 1', 'type_evaluation' => $type,
        'max_note' => $max, 'date_evaluation' => '2026-10-01', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('notes')->insert([
        'evaluation_id' => $evaluationId, 'eleve_id' => $eleveId, 'note_obtenue' => $obtenu,
        'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

test('the calculator applies the percentage rule and pass threshold', function () {
    $inscriptionId = seedBulletinEleve();
    $eleveId = (int) DB::table('inscriptions')->where('id', $inscriptionId)->value('eleve_id');
    // 16/20 (TJ) + 60/80 (Examen) = 76/100 = 76 % -> Passe
    seedNote($eleveId, 'interro', 20, 16);
    seedNote($eleveId, 'examen', 80, 60);

    $data = BulletinRdc::for(Inscription::find($inscriptionId))->build();

    expect($data['branches'][0]['total'])->toBe(76.0)
        ->and($data['branches'][0]['max'])->toBe(100.0)
        ->and($data['branches'][0]['pourcentage'])->toBe(76.0)
        ->and($data['total_general'])->toBe(76.0)
        ->and($data['max_general'])->toBe(100.0)
        ->and($data['pourcentage_general'])->toBe(76.0)
        ->and($data['decision'])->toBe('Passe')
        ->and($data['branches_repechage'])->toBe([]);
});

test('a student below the pass threshold is declared failed and listed for retake', function () {
    $inscriptionId = seedBulletinEleve();
    $eleveId = (int) DB::table('inscriptions')->where('id', $inscriptionId)->value('eleve_id');
    // 4/20 = 20 % -> Double, branche en repêchage
    seedNote($eleveId, 'interro', 20, 4);

    $data = BulletinRdc::for(Inscription::find($inscriptionId))->build();

    expect($data['pourcentage_general'])->toBe(20.0)
        ->and($data['decision'])->toBe('Double')
        ->and($data['branches_repechage'])->toBe(['Algèbre']);
});

test('the bulletin document renders the official layout with the computed results', function () {
    $inscriptionId = seedBulletinEleve();
    $eleveId = (int) DB::table('inscriptions')->where('id', $inscriptionId)->value('eleve_id');
    seedNote($eleveId, 'interro', 20, 16);
    seedNote($eleveId, 'examen', 80, 60);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.bulletins.document', $inscriptionId))
        ->assertOk()
        ->assertSee('République Démocratique du Congo')
        ->assertSee('Mwamba Kabila Aline')
        ->assertSee('Algèbre')
        ->assertSee('76 %')
        ->assertSee('PASSE');
});

test('the bulletin document is restricted to administrators', function () {
    $inscriptionId = seedBulletinEleve();
    $professeur = User::factory()->create(['role' => 'professeur']);

    $this->actingAs($professeur)
        ->get(route('admin.bulletins.document', $inscriptionId))
        ->assertForbidden();
});

test('the bulletins list links to the RDC document', function () {
    $inscriptionId = seedBulletinEleve();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.bulletins.index', ['periode_id' => 1, 'classe_id' => 1]))
        ->assertOk()
        ->assertSee(route('admin.bulletins.document', $inscriptionId), false);
});

test('the reference maximum of a branch is displayed even without any note', function () {
    $inscriptionId = seedBulletinEleve();
    // Barème de la branche : T.J. 20 + Examen 40 = 60 par période.
    DB::table('cours')->where('id', 1)->update(['max_travaux_journaliers' => 20, 'max_examen' => 40]);

    $data = BulletinRdc::for(Inscription::find($inscriptionId))->build();

    expect($data['branches'][0]['max'])->toBe(60.0)
        ->and($data['branches'][0]['periodes'][1]['tj_max'])->toBe(20.0)
        ->and($data['branches'][0]['periodes'][1]['examen_max'])->toBe(40.0)
        ->and($data['branches'][0]['periodes'][1]['total'])->toBe(0.0)
        ->and($data['max_general'])->toBe(60.0);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.bulletins.document', $inscriptionId))
        ->assertOk()
        ->assertSee('M.J.')
        ->assertSee('M.EX.')
        ->assertSee('60');
});

test('the branch maximums can be recorded from the course form', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.cours.store'), [
            'code' => 'GEO', 'intitule' => 'Géographie', 'ponderation' => 2,
            'max_travaux_journaliers' => 10, 'max_examen' => 20,
        ])
        ->assertRedirect(route('admin.cours.index'));

    $cours = DB::table('cours')->where('code', 'GEO')->first();
    expect((int) $cours->max_travaux_journaliers)->toBe(10)
        ->and((int) $cours->max_examen)->toBe(20);
});

test('the settings screen saves the application options and the bulletin settings', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.profile.settings'))
        ->assertOk()
        ->assertSee('Options de l’application')
        ->assertSee('Bulletin (en-tête, pied de page, règles)');

    $this->actingAs($admin)
        ->put(route('admin.profile.settings.update'), [
            'nom_etablissement' => 'COMPLEXE SCOLAIRE BABAZIN',
            'nom_court' => 'C.S. Moty',
            'saisie_notes_active' => '0',
            'portail_parents_actif' => '1',
            'deliberation_active' => '1',
            'bulletin_pays' => 'République Démocratique du Congo',
            'bulletin_ministere' => "Ministère de l'EPSP",
            'bulletin_titre' => 'BULLETIN ANNUEL — CTEB',
            'bulletin_reference' => 'EBE/P.S./012',
            'bulletin_province' => 'Kinshasa',
            'bulletin_ville' => 'Kinshasa',
            'bulletin_commune' => 'Limete',
            'bulletin_code_ecole' => 'KIN-042',
            'bulletin_lieu_signature' => 'Kinshasa',
            'bulletin_signataire' => 'Le Directeur',
            'bulletin_seuil_reussite' => '60',
            'bulletin_afficher_place' => '1',
            'bulletin_afficher_conduite' => '0',
        ])
        ->assertRedirect(route('admin.profile.settings'));

    $settings = ParametreSysteme::query()->first();

    expect((int) $settings->saisie_notes_active)->toBe(0)
        ->and((int) $settings->portail_parents_actif)->toBe(1)
        ->and((int) $settings->deliberation_active)->toBe(1)
        ->and($settings->bulletin_titre)->toBe('BULLETIN ANNUEL — CTEB')
        ->and($settings->bulletin_signataire)->toBe('Le Directeur')
        ->and((float) $settings->bulletin_seuil_reussite)->toBe(60.0)
        ->and((int) $settings->bulletin_afficher_place)->toBe(1)
        ->and((int) $settings->bulletin_afficher_conduite)->toBe(0);

    // Un envoi partiel (identité seule) ne doit pas effacer les réglages du bulletin.
    $this->actingAs($admin)
        ->put(route('admin.profile.settings.update'), [
            'nom_etablissement' => 'INSTITUT MWANGAZA',
            'nom_court' => 'I.M. MWANGAZA',
        ])
        ->assertRedirect(route('admin.profile.settings'));

    expect(ParametreSysteme::query()->first()->bulletin_titre)->toBe('BULLETIN ANNUEL — CTEB');
});

test('the bulletin document uses the saved parameters and pass threshold', function () {
    $inscriptionId = seedBulletinEleve();
    $eleveId = (int) DB::table('inscriptions')->where('id', $inscriptionId)->value('eleve_id');
    // 16/20 + 60/80 = 76 % : passe avec un seuil de 50 %, double avec un seuil de 90 %.
    seedNote($eleveId, 'interro', 20, 16);
    seedNote($eleveId, 'examen', 80, 60);

    ParametreSysteme::create([
        'nom_etablissement' => 'COMPLEXE SCOLAIRE MOTY',
        'nom_court' => 'C.S. Moty',
        'bulletin_pays' => 'République Démocratique du Congo',
        'bulletin_ministere' => 'Ministère de l’EPSP',
        'bulletin_titre' => 'BULLETIN ANNUEL — CTEB',
        'bulletin_reference' => 'EBE/P.S./012',
        'bulletin_province' => 'Kinshasa',
        'bulletin_ville' => 'Kinshasa',
        'bulletin_commune' => 'Limete',
        'bulletin_code_ecole' => 'KIN-042',
        'bulletin_lieu_signature' => 'Kinshasa',
        'bulletin_signataire' => 'Le Directeur',
        'bulletin_seuil_reussite' => 90,
        'bulletin_afficher_place' => true,
        'bulletin_afficher_conduite' => false,
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.bulletins.document', $inscriptionId))
        ->assertOk()
        ->assertSee('BULLETIN ANNUEL — CTEB')
        ->assertSee('KIN-042')
        ->assertSee('Limete')
        ->assertSee('Le Directeur')
        ->assertSee('EBE/P.S./012')
        ->assertSee('PLACE / NBRE D')
        ->assertDontSee('CONDUITE')
        ->assertSee('*DOUBLE');

    $data = BulletinRdc::for(Inscription::find($inscriptionId))->build();

    expect($data['decision'])->toBe('Double')
        ->and($data['seuil_reussite'])->toBe(90.0)
        ->and($data['branches_repechage'])->toBe(['Algèbre']);
});

test('la note maximale d’une évaluation est reprise du barème du cours si elle est omise', function () {
    DB::table('cours')->where('id', 1)->update(['max_travaux_journaliers' => 30, 'max_examen' => 60]);
    $admin = User::factory()->create(['role' => 'admin']);

    foreach ([['interro', 30.0], ['examen', 60.0]] as [$type, $attendu]) {
        $this->actingAs($admin)
            ->post(route('admin.evaluations.store'), [
                'affectation_id' => 1, 'periode_id' => 1, 'titre' => 'Eval '.$type,
                'type_evaluation' => $type, 'date_evaluation' => '2026-10-01',
                // max_note volontairement omis → repris du barème de la branche.
            ])
            ->assertRedirect(route('admin.evaluations.index'));

        $evaluation = DB::table('evaluations')->where('titre', 'Eval '.$type)->first();
        expect((float) $evaluation->max_note)->toBe($attendu);
    }
});

test('l’en-tête du bulletin affiche le cycle réel de la classe et l’année scolaire', function () {
    DB::table('parametres_systeme')->delete(); // aucun titre imposé → titre déduit du cycle
    $inscriptionId = seedBulletinEleve();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.bulletins.document', $inscriptionId))
        ->assertOk()
        ->assertSee('Bulletin annuel — Secondaire')
        ->assertSee('ANNÉE SCOLAIRE 2026-2027');
});

test('l’identité du bulletin reprend cycle et section sans redite', function () {
    $avecStructure = function (string $cycle, string $section, ?string $option): \App\Models\Classe {
        $classe = new \App\Models\Classe();
        $classe->nom = $cycle;

        $modeleCycle = new \App\Models\Cycle();
        $modeleCycle->nom = $cycle;
        $classe->setRelation('cycle', $modeleCycle);

        $modeleSection = new \App\Models\Section();
        $modeleSection->nom = $section;
        $classe->setRelation('section', $modeleSection);

        if ($option !== null) {
            $modeleOption = new \App\Models\Option();
            $modeleOption->nom = $option;
            $classe->setRelation('option', $modeleOption);
        }

        return $classe;
    };

    // Option identique à la section → écartée.
    expect(\App\Support\StructureClasse::libelle($avecStructure('Humanité', 'Commerciale et administrative', 'Commerciale et administrative')))
        ->toBe('Humanité — Commerciale et administrative');

    // Option redondante (chaîne différente mais contenant la section) → écartée aussi.
    expect(\App\Support\StructureClasse::libelle($avecStructure('E.d base', 'Education de base', 'E.B (education de base )')))
        ->toBe('E.d base — Education de base');

    // Section qui reprend le cycle → écartée.
    expect(\App\Support\StructureClasse::libelle($avecStructure('Primaire', 'Primaire de base', null)))
        ->toBe('Primaire');
});

test('le bulletin porte un QR unique et n’affiche pas la photo', function () {
    $inscriptionId = seedBulletinEleve();
    $admin = User::factory()->create(['role' => 'admin']);

    // La photo est suspendue : même renseignée, elle ne doit pas apparaître.
    DB::table('eleves')->update(['photo' => 'users/photos/eleve-1.jpg']);

    $this->actingAs($admin)
        ->get(route('admin.bulletins.document', $inscriptionId))
        ->assertOk()
        ->assertSee('id="bulletin-qr"', false)
        ->assertSee('js/qrcode.min.js', false)
        ->assertSee('BUL-20262027-000001-')
        ->assertSeeInOrder(['bas-page', 'id="bulletin-qr"'], false)
        ->assertDontSee('users/photos/eleve-1.jpg', false)
        ->assertDontSee('aria-label="Photo de l’élève"', false);
});

test('le code du bulletin unique est stable et figure dans la charge du QR', function () {
    $inscriptionId = seedBulletinEleve();
    $inscription = \App\Models\Inscription::with(['eleve', 'anneeScolaire'])->findOrFail($inscriptionId);

    $donnees = \App\Support\BulletinUnique::donnees($inscription);

    expect($donnees['code'])->toStartWith('BUL-20262027-000001-')
        ->and(strlen($donnees['code']))->toBe(28)                       // BUL- + 8 + -6 + -8
        ->and($donnees['charge'])->toContain($donnees['code'])          // le QR encode le code
        ->and($donnees['charge'])->toContain('ELV-2026-0001')           // …et le matricule
        ->and($donnees['charge'])->toContain('2026-2027')               // …et l'année
        ->and(\App\Support\BulletinUnique::code($inscription))->toBe($donnees['code']); // stable
});
