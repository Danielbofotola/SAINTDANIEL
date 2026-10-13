<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'archives_annees', 'paies', 'salaires', 'bulletins', 'notes', 'evaluations', 'affectations_cours',
        'depenses', 'categories_depenses', 'paiements', 'frais_scolaires',
        'types_frais', 'inscriptions', 'periodes', 'eleves', 'tuteurs', 'classes', 'cycles', 'salles',
        'professeurs', 'annees_scolaires', 'users',
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
    Schema::create('professeurs', function (Blueprint $table) {
        $table->id();
        $table->string('matricule')->unique();
        $table->string('nom', 50);
        $table->string('postnom', 50);
        $table->string('prenom', 50);
        $table->enum('sexe', ['M', 'F']);
        $table->string('specialite', 100);
        $table->string('telephone', 20);
        $table->string('email', 100)->unique();
        $table->string('statut')->default('actif');
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
        $table->unsignedBigInteger('section_id')->nullable();
        $table->unsignedBigInteger('option_id')->nullable();
        $table->string('nom');
        $table->string('statut');
        $table->timestamps();
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
    Schema::create('types_frais', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 100);
        $table->text('description')->nullable();
        $table->boolean('est_inscription')->default(false);
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });
    Schema::create('frais_scolaires', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('type_frais_id');
        $table->unsignedBigInteger('classe_id');
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->decimal('montant_exige', 10, 2);
        $table->date('date_echeance');
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });
    Schema::create('periodes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('nom', 50);
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
        $table->enum('mode_paiement', ['especes', 'banque', 'mobile_money'])->default('especes');
        $table->string('reference_transaction', 100)->nullable();
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });
    Schema::create('categories_depenses', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 100);
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
        $table->timestamps();
    });
    Schema::create('depenses', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->unsignedBigInteger('category_id');
        $table->string('motif');
        $table->decimal('montant', 10, 2);
        $table->date('date_depense');
        $table->string('piece_justificative')->nullable();
        $table->enum('statut', ['actif', 'inactif'])->default('actif');
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
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('bulletins', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('inscription_id');
        $table->string('periode')->nullable();
        $table->decimal('moyenne_generale', 5, 2)->nullable();
        $table->string('decision')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('salaires', function (Blueprint $table) {
        $table->id();
        $table->string('beneficiaire_type', 20);
        $table->unsignedBigInteger('beneficiaire_id');
        $table->decimal('salaire_base', 12, 2);
        $table->string('devise', 10)->default('USD');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('paies', function (Blueprint $table) {
        $table->id();
        $table->string('num_paie', 50)->unique();
        $table->unsignedBigInteger('salaire_id');
        $table->string('periode', 7);
        $table->decimal('montant_base', 12, 2);
        $table->decimal('primes', 12, 2)->default(0);
        $table->decimal('retenues', 12, 2)->default(0);
        $table->decimal('montant_net', 12, 2);
        $table->date('date_paiement');
        $table->string('mode_paiement', 20)->default('especes');
        $table->string('reference', 100)->nullable();
        $table->string('observation')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('archives_annees', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('annee_scolaire_id')->unique();
        $table->unsignedInteger('total_eleves_inscrits')->default(0);
        $table->decimal('total_recettes', 12, 2)->default(0);
        $table->decimal('total_depenses', 12, 2)->default(0);
        $table->decimal('solde_final', 12, 2)->default(0);
        $table->decimal('total_attendu', 12, 2)->default(0);
        $table->decimal('total_impayes', 12, 2)->default(0);
        $table->decimal('total_salaires', 12, 2)->default(0);
        $table->unsignedInteger('nombre_paies')->default(0);
        $table->unsignedInteger('eleves_garcons')->default(0);
        $table->unsignedInteger('eleves_filles')->default(0);
        $table->unsignedInteger('total_bulletins')->default(0);
        $table->unsignedInteger('total_notes')->default(0);
        $table->dateTime('date_cloture')->nullable();
        $table->text('remarques')->nullable();
        $table->timestamp('created_at')->nullable();
    });

    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Secondaire', 'statut' => 'actif']);
    DB::table('salles')->insert(['id' => 1, 'nom' => 'Salle 1', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'cycle_id' => 1, 'salle_id' => 1, 'nom' => '3e A', 'statut' => 'actif']);
    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif',
    ]);
});

function seedInscription(): int
{
    $tuteurId = DB::table('tuteurs')->insertGetId([
        'nom_mere' => 'Marie', 'telephone_mere' => '0990000000', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $eleveId = DB::table('eleves')->insertGetId([
        'matricule' => 'ELV-2026-0001', 'nom' => 'Mwamba', 'postnom' => 'Test', 'prenom' => 'Aline',
        'sexe' => 'F', 'date_naissance' => '2014-05-12', 'tuteur_id' => $tuteurId, 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return DB::table('inscriptions')->insertGetId([
        'eleve_id' => $eleveId, 'classe_id' => 1, 'annee_scolaire_id' => 1,
        'date_inscription' => '2026-09-05', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

function seedFrais(string $montant = '150.00'): int
{
    $typeId = DB::table('types_frais')->insertGetId([
        'nom' => 'Frais scolaires', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);

    return DB::table('frais_scolaires')->insertGetId([
        'type_frais_id' => $typeId, 'classe_id' => 1, 'annee_scolaire_id' => 1,
        'montant_exige' => $montant, 'date_echeance' => '2026-10-31', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

test('an admin can manage fee types and class tariffs', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.frais.types.store'), ['nom' => 'Frais scolaires', 'description' => 'Frais annuels'])
        ->assertRedirect(route('admin.frais.index'));
    $this->assertDatabaseHas('types_frais', ['nom' => 'Frais scolaires']);

    $this->actingAs($admin)
        ->post(route('admin.frais.types.store'), ['nom' => 'Frais scolaires'])
        ->assertSessionHasErrors('nom');

    $typeId = (int) DB::table('types_frais')->where('nom', 'Frais scolaires')->value('id');

    $this->actingAs($admin)
        ->post(route('admin.frais.tarifs.store'), [
            'type_frais_id' => $typeId, 'classe_id' => 1, 'montant_exige' => 150, 'date_echeance' => '2026-10-31',
        ])
        ->assertRedirect(route('admin.frais.index'));
    $this->assertDatabaseHas('frais_scolaires', ['type_frais_id' => $typeId, 'classe_id' => 1, 'annee_scolaire_id' => 1]);

    // Doublon (même type, même classe, même année) refusé.
    $this->actingAs($admin)
        ->post(route('admin.frais.tarifs.store'), [
            'type_frais_id' => $typeId, 'classe_id' => 1, 'montant_exige' => 200, 'date_echeance' => '2026-11-30',
        ])
        ->assertSessionHas('error');
    expect(DB::table('frais_scolaires')->count())->toBe(1);

    // Type utilisé par un tarif : suppression bloquée.
    $this->actingAs($admin)
        ->delete(route('admin.frais.types.destroy', $typeId))
        ->assertSessionHas('error');
    expect(DB::table('types_frais')->count())->toBe(1);
});

test('a payment is recorded with a receipt and must match the student class', function () {
    $inscriptionId = seedInscription();
    $fraisId = seedFrais();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.paiements.store'), [
            'inscription_id' => $inscriptionId, 'frais_scolaire_id' => $fraisId,
            'montant_paye' => 75, 'mode_paiement' => 'especes',
        ])
        ->assertRedirect(route('admin.paiements.index'));

    $paiement = DB::table('paiements')->first();
    expect($paiement)->not->toBeNull()
        ->and($paiement->num_recu)->toBe('REC-2026-2027-0001')
        ->and((float) $paiement->montant_paye)->toBe(75.0);

    // Tarif d'une autre classe -> refusé.
    DB::table('classes')->insert(['id' => 2, 'cycle_id' => 1, 'nom' => '4e B', 'statut' => 'actif']);
    $autreFrais = DB::table('frais_scolaires')->insertGetId([
        'type_frais_id' => DB::table('types_frais')->value('id'), 'classe_id' => 2, 'annee_scolaire_id' => 1,
        'montant_exige' => 120, 'date_echeance' => '2026-10-31', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.paiements.store'), [
            'inscription_id' => $inscriptionId, 'frais_scolaire_id' => $autreFrais,
            'montant_paye' => 50, 'mode_paiement' => 'banque',
        ])
        ->assertSessionHas('error');

    expect(DB::table('paiements')->count())->toBe(1);
});

test('la page de paiement associe chaque élève à sa classe pour ne proposer que ses tarifs', function () {
    $inscriptionId = seedInscription();
    seedFrais();
    $admin = User::factory()->create(['role' => 'admin']);

    // Une classe et un tarif d'une AUTRE classe, à ne pas rattacher à l'élève.
    DB::table('classes')->insert(['id' => 2, 'cycle_id' => 1, 'nom' => '4e B', 'statut' => 'actif']);
    DB::table('frais_scolaires')->insert([
        'type_frais_id' => DB::table('types_frais')->value('id'), 'classe_id' => 2, 'annee_scolaire_id' => 1,
        'montant_exige' => 120, 'date_echeance' => '2026-10-31', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    // Liaisons transmises au JS : id d'inscription → id de classe, et tarif → classe.
    $this->actingAs($admin)->get(route('admin.paiements.index'))
        ->assertOk()
        ->assertSee('"'.$inscriptionId.'":1', false)
        ->assertSee('"classe":1', false)
        ->assertSee('"classe":2', false);
});

test('expenses and categories are managed per school year', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.depenses.categories.store'), ['nom' => 'Fournitures'])
        ->assertRedirect(route('admin.depenses.index'));
    $categorieId = (int) DB::table('categories_depenses')->value('id');

    $this->actingAs($admin)
        ->post(route('admin.depenses.store'), [
            'category_id' => $categorieId, 'motif' => 'Achat craies', 'montant' => 25, 'date_depense' => '2026-10-02',
        ])
        ->assertRedirect(route('admin.depenses.index'));

    $depense = DB::table('depenses')->first();
    expect($depense)->not->toBeNull()
        ->and((int) $depense->annee_scolaire_id)->toBe(1)
        ->and((float) $depense->montant)->toBe(25.0);

    // Catégorie utilisée : suppression bloquée.
    $this->actingAs($admin)
        ->delete(route('admin.depenses.categories.destroy', $categorieId))
        ->assertSessionHas('error');
    expect(DB::table('categories_depenses')->count())->toBe(1);
});

test('closing a school year computes the summary and can be reopened', function () {
    $inscriptionId = seedInscription();
    $fraisId = seedFrais();
    DB::table('paiements')->insert([
        'num_recu' => 'REC-2026-2027-0001', 'inscription_id' => $inscriptionId, 'frais_scolaire_id' => $fraisId,
        'montant_paye' => 150, 'date_paiement' => now(), 'mode_paiement' => 'especes', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $categorieId = DB::table('categories_depenses')->insertGetId(['nom' => 'Entretien', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('depenses')->insert([
        'annee_scolaire_id' => 1, 'category_id' => $categorieId, 'motif' => 'Réparations', 'montant' => 40,
        'date_depense' => '2026-10-03', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
    // Un salaire versé pendant l'année : il doit entrer dans la clôture.
    DB::table('salaires')->insert([
        'id' => 1, 'beneficiaire_type' => 'professeur', 'beneficiaire_id' => 1,
        'salaire_base' => 100, 'devise' => 'USD', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('paies')->insert([
        'num_paie' => 'PAIE-2026-10-0001', 'salaire_id' => 1, 'periode' => '2026-10',
        'montant_base' => 100, 'primes' => 10, 'retenues' => 0, 'montant_net' => 110,
        'date_paiement' => '2026-10-05', 'mode_paiement' => 'especes', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.archives.store'), ['annee_scolaire_id' => 1, 'remarques' => 'Clôture de test'])
        ->assertRedirect(route('admin.archives.index'));

    $archive = DB::table('archives_annees')->first();
    expect($archive)->not->toBeNull()
        ->and((int) $archive->total_eleves_inscrits)->toBe(1)
        ->and((float) $archive->total_recettes)->toBe(150.0)
        ->and((float) $archive->total_depenses)->toBe(40.0)
        ->and((float) $archive->total_salaires)->toBe(110.0)
        ->and((int) $archive->nombre_paies)->toBe(1)
        ->and((float) $archive->solde_final)->toBe(0.0)
        ->and((float) $archive->total_attendu)->toBe(150.0)
        ->and((float) $archive->total_impayes)->toBe(0.0)
        ->and((int) $archive->eleves_garcons)->toBe(0)
        ->and((int) $archive->eleves_filles)->toBe(1)
        ->and((int) $archive->total_bulletins)->toBe(0)
        ->and((int) $archive->total_notes)->toBe(0);

    // Deuxième clôture de la même année -> refusée.
    $this->actingAs($admin)
        ->post(route('admin.archives.store'), ['annee_scolaire_id' => 1])
        ->assertSessionHasErrors('annee_scolaire_id');
    expect(DB::table('archives_annees')->count())->toBe(1);

    // Réouverture.
    $this->actingAs($admin)
        ->delete(route('admin.archives.destroy', $archive->id))
        ->assertRedirect(route('admin.archives.index'));
    expect(DB::table('archives_annees')->count())->toBe(0);
});

test('the payments page offers a student filter and shows the remaining balance', function () {
    $inscriptionId = seedInscription();
    $fraisId = seedFrais('150.00');
    $admin = User::factory()->create(['role' => 'admin']);

    // Règlement d'une tranche : 90 payés sur 150 exigés -> 60 restants.
    DB::table('paiements')->insert([
        'num_recu' => 'REC-2026-2027-0001', 'inscription_id' => $inscriptionId, 'frais_scolaire_id' => $fraisId,
        'montant_paye' => 90, 'date_paiement' => now(), 'mode_paiement' => 'especes', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.paiements.index'))
        ->assertOk()
        ->assertSee('eleveSearch')
        ->assertSee('Reste à payer')
        ->assertSee('60,00');
});

test('the finance menu links point to the new modules', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.frais.index'), false)
        ->assertSee(route('admin.paiements.index'), false)
        ->assertSee(route('admin.depenses.index'), false)
        ->assertSee(route('admin.archives.index'), false);
});

test('the admin profile sidebar links to the management modules', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.profile.show'))
        ->assertOk()
        ->assertSee(route('admin.affectations.index'), false)
        ->assertSee(route('admin.periodes.index'), false)
        ->assertSee(route('admin.evaluations.index'), false)
        ->assertSee(route('admin.bulletins.index'), false)
        ->assertSee(route('admin.archives.index'), false)
        ->assertSee(route('admin.eleves.index'), false)
        ->assertSee(route('admin.frais.index'), false)
        ->assertSee(route('admin.paiements.index'), false)
        ->assertSee(route('admin.depenses.index'), false)
        ->assertSee(route('admin.professeurs.index'), false)
        ->assertSee(route('admin.emplois-du-temps.index'), false);
});

test('a closed school year can be browsed in read-only', function () {
    $inscriptionId = seedInscription();
    $fraisId = seedFrais('150.00');
    DB::table('paiements')->insert([
        'num_recu' => 'REC-2026-2027-0001', 'inscription_id' => $inscriptionId, 'frais_scolaire_id' => $fraisId,
        'montant_paye' => 90, 'date_paiement' => now(), 'mode_paiement' => 'especes', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $categorieId = DB::table('categories_depenses')->insertGetId(['nom' => 'Entretien', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('depenses')->insert([
        'annee_scolaire_id' => 1, 'category_id' => $categorieId, 'motif' => 'Réparations', 'montant' => 25,
        'date_depense' => '2026-10-03', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $archiveId = DB::table('archives_annees')->insertGetId([
        'annee_scolaire_id' => 1, 'total_eleves_inscrits' => 1, 'total_recettes' => 90,
        'total_depenses' => 25, 'solde_final' => 65, 'date_cloture' => now(),
        'remarques' => 'Clôture de test', 'created_at' => now(),
    ]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.archives.index'))
        ->assertOk()
        ->assertSee(route('admin.archives.show', $archiveId), false);

    $this->actingAs($admin)
        ->get(route('admin.archives.show', $archiveId))
        ->assertOk()
        ->assertSee('Année clôturée')
        ->assertSee('Mwamba')
        ->assertSee('3e A')
        ->assertSee('90,00')
        ->assertSee('Entretien');
    });

test('an admin can open the receipt of a payment', function () {
    $inscriptionId = seedInscription();
    $fraisId = seedFrais('150.00');
    DB::table('paiements')->insert([
        'num_recu' => 'REC-2026-2027-0001', 'inscription_id' => $inscriptionId, 'frais_scolaire_id' => $fraisId,
        'montant_paye' => 40, 'date_paiement' => now(), 'mode_paiement' => 'especes', 'statut' => 'actif',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $paiementId = DB::table('paiements')->value('id');
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.paiements.index'))
        ->assertOk()
        ->assertSee(route('admin.paiements.recu', $paiementId), false);

    $this->actingAs($admin)
        ->get(route('admin.paiements.recu', $paiementId))
        ->assertOk()
        ->assertSee('REC-2026-2027-0001')
        ->assertSee('Généré automatiquement');
});
