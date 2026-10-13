<?php

use App\Models\AnneeScolaire;
use App\Models\Certification;
use App\Models\Classe;
use App\Models\Cycle;
use App\Models\Deliberation;
use App\Models\Eleve;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fin de cycle (Cycle.ordre), diplômation en fin du dernier cycle, et nettoyage
 * des inscriptions restées « en attente ».
 */
beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'certifications', 'classe_destinations', 'deliberations', 'notes', 'evaluations', 'periodes',
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
        $table->string('fin_type', 20)->default('aucune');
        $table->string('fin_libelle', 100)->nullable();
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
    Schema::create('certifications', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('libelle', 100);
        $table->date('date_obtention');
        $table->timestamps();
        $table->unique(['eleve_id', 'cycle_id', 'annee_scolaire_id']);
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
 * Primaire (ordre 1) : 5e, 6e — Humanités (ordre 2, dernier) : 1re, 4e (terminale).
 *
 * @return array{admin: User, p6: Classe, h1: Classe, h4: Classe, annee: AnneeScolaire, cible: AnneeScolaire}
 */
function scenarioCycles(): array
{
    $admin = User::factory()->create(['role' => 'admin']);
    $annee = AnneeScolaire::create(['libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-15', 'is_encours' => true, 'statut' => 'actif']);
    $cible = AnneeScolaire::create(['libelle' => '2027-2028', 'date_debut' => '2027-09-01', 'date_fin' => '2028-07-15', 'is_encours' => false, 'statut' => 'actif']);

    $primaire = DB::table('cycles')->insertGetId(['nom' => 'Primaire', 'ordre' => 1, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    $humanites = DB::table('cycles')->insertGetId(['nom' => 'Humanités', 'ordre' => 2, 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);

    $p5 = Classe::create(['cycle_id' => $primaire, 'nom' => '5e primaire', 'ordre' => 5, 'statut' => 'actif']);
    $p6 = Classe::create(['cycle_id' => $primaire, 'nom' => '6e primaire', 'ordre' => 6, 'statut' => 'actif']);
    $h1 = Classe::create(['cycle_id' => $humanites, 'nom' => '1re humanité', 'ordre' => 1, 'statut' => 'actif']);
    $h4 = Classe::create(['cycle_id' => $humanites, 'nom' => '4e humanité', 'ordre' => 4, 'statut' => 'actif']);

    return ['admin' => $admin, 'primaire' => $primaire, 'humanites' => $humanites, 'p5' => $p5, 'p6' => $p6, 'h1' => $h1, 'h4' => $h4, 'annee' => $annee, 'cible' => $cible];
}

test('l’ordre des cycles détermine le cycle suivant et le dernier cycle', function () {
    $s = scenarioCycles();

    expect(Cycle::find($s['primaire'])->suivant()?->id)->toBe($s['humanites'])
        ->and(Cycle::find($s['primaire'])->estDernier())->toBeFalse()
        ->and(Cycle::find($s['humanites'])->estDernier())->toBeTrue();
});

test('une classe de fin de cycle mène automatiquement au 1er niveau du cycle suivant', function () {
    $s = scenarioCycles();

    // 6e primaire : plus de niveau au-dessus dans le cycle → 1re humanité.
    expect($s['p6']->estTerminale())->toBeFalse()
        ->and($s['p6']->candidats()->pluck('id')->all())->toBe([$s['h1']->id]);

    // 4e humanité : dernier cycle, aucune classe d'accueil → terminale.
    expect($s['h4']->estTerminale())->toBeTrue()
        ->and($s['h4']->candidats())->toBeEmpty();
});

test('un élève diplômé sort avec le statut diplome, sans réinscription', function () {
    $s = scenarioCycles();
    $eleve = Eleve::create(['matricule' => 'ELV-9', 'nom' => 'Sortant', 'prenom' => 'Jean', 'sexe' => 'M', 'statut' => 'actif']);
    $inscription = Inscription::create(['eleve_id' => $eleve->id, 'classe_id' => $s['h4']->id, 'annee_scolaire_id' => $s['annee']->id, 'date_inscription' => '2026-09-05', 'statut' => Inscription::STATUT_ACTIF]);
    Deliberation::create(['inscription_id' => $inscription->id, 'annee_scolaire_id' => $s['annee']->id, 'decision' => 'diplome', 'classe_suivante_id' => null]);

    $this->actingAs($s['admin'])->post(route('admin.deliberations.appliquer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'annee_cible_id' => $s['cible']->id,
    ])->assertRedirect()->assertSessionHas('success', fn ($m) => str_contains($m, '1 diplômé(s)'));

    expect($eleve->fresh()->statut)->toBe('diplome')
        ->and(Inscription::where('annee_scolaire_id', $s['cible']->id)->count())->toBe(0);
});

test('un admis sur la classe terminale du dernier cycle est diplômé automatiquement', function () {
    $s = scenarioCycles();
    $eleve = Eleve::create(['matricule' => 'ELV-10', 'nom' => 'Finaliste', 'prenom' => 'Ada', 'sexe' => 'F', 'statut' => 'actif']);
    $inscription = Inscription::create(['eleve_id' => $eleve->id, 'classe_id' => $s['h4']->id, 'annee_scolaire_id' => $s['annee']->id, 'date_inscription' => '2026-09-05', 'statut' => Inscription::STATUT_ACTIF]);
    Deliberation::create(['inscription_id' => $inscription->id, 'annee_scolaire_id' => $s['annee']->id, 'decision' => 'admis']);

    $this->actingAs($s['admin'])->post(route('admin.deliberations.appliquer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'annee_cible_id' => $s['cible']->id,
    ])->assertRedirect();

    expect($eleve->fresh()->statut)->toBe('diplome')
        ->and(Deliberation::where('inscription_id', $inscription->id)->first()->decision)->toBe('diplome')
        ->and(Inscription::where('annee_scolaire_id', $s['cible']->id)->count())->toBe(0);
});

test('supprimer une inscription en attente conserve l’élève et son tuteur', function () {
    $s = scenarioCycles();
    $eleve = Eleve::create(['matricule' => 'ELV-11', 'nom' => 'EnAttente', 'prenom' => 'Paul', 'sexe' => 'M', 'statut' => 'actif']);
    $inscription = Inscription::create(['eleve_id' => $eleve->id, 'classe_id' => $s['p5']->id, 'annee_scolaire_id' => $s['annee']->id, 'date_inscription' => '2026-09-05', 'statut' => Inscription::STATUT_EN_ATTENTE]);

    $this->actingAs($s['admin'])->get(route('admin.inscriptions.attente'))->assertOk()->assertSee('EnAttente');

    $this->actingAs($s['admin'])->delete(route('admin.inscriptions.attente.destroy', $inscription))
        ->assertRedirect()->assertSessionHas('success');

    expect(Inscription::find($inscription->id))->toBeNull()
        ->and(Eleve::find($eleve->id))->not->toBeNull();
});

test('la purge retire toutes les inscriptions en attente de l’année', function () {
    $s = scenarioCycles();
    $eleve = Eleve::create(['matricule' => 'ELV-12', 'nom' => 'Purge', 'prenom' => 'Ana', 'sexe' => 'F', 'statut' => 'actif']);
    Inscription::create(['eleve_id' => $eleve->id, 'classe_id' => $s['p5']->id, 'annee_scolaire_id' => $s['annee']->id, 'date_inscription' => '2026-09-05', 'statut' => Inscription::STATUT_EN_ATTENTE]);
    // Un inscrit actif ne doit PAS être emporté par la purge.
    $actif = Eleve::create(['matricule' => 'ELV-13', 'nom' => 'Actif', 'prenom' => 'Lin', 'sexe' => 'M', 'statut' => 'actif']);
    Inscription::create(['eleve_id' => $actif->id, 'classe_id' => $s['p5']->id, 'annee_scolaire_id' => $s['annee']->id, 'date_inscription' => '2026-09-05', 'statut' => Inscription::STATUT_ACTIF]);

    $this->actingAs($s['admin'])->delete(route('admin.inscriptions.attente.purge'))
        ->assertRedirect()->assertSessionHas('success', fn ($m) => str_contains($m, '1 inscription(s)'));

    expect(Inscription::where('statut', Inscription::STATUT_EN_ATTENTE)->count())->toBe(0)
        ->and(Inscription::where('statut', Inscription::STATUT_ACTIF)->count())->toBe(1);
});

test('le formulaire de cycle enregistre le rang et la fin de cycle', function () {
    $s = scenarioCycles();

    $this->actingAs($s['admin'])->patch(route('admin.cycles.update', $s['primaire']), [
        'ordre' => 1,
        'fin_type' => 'certificat',
        'fin_libelle' => "Certificat d'études primaires",
    ])->assertRedirect();

    $cycle = Cycle::find($s['primaire']);

    expect($cycle->fin_type)->toBe('certificat')
        ->and($cycle->fin_libelle)->toBe("Certificat d'études primaires")
        ->and($cycle->delivreCertificat())->toBeTrue();
});

test('un cycle à certificat fait poursuivre l’élève au cycle suivant', function () {
    $s = scenarioCycles();
    DB::table('cycles')->where('id', $s['primaire'])->update(['fin_type' => 'certificat', 'fin_libelle' => "Certificat d'études primaires"]);

    // Élève en 6e primaire : fin du cycle primaire, poursuit en 1re humanité.
    $eleve = Eleve::create(['matricule' => 'ELV-20', 'nom' => 'Certifie', 'prenom' => 'Aline', 'sexe' => 'F', 'statut' => 'actif']);
    $inscription = Inscription::create(['eleve_id' => $eleve->id, 'classe_id' => $s['p6']->id, 'annee_scolaire_id' => $s['annee']->id, 'date_inscription' => '2026-09-05', 'statut' => Inscription::STATUT_ACTIF]);
    Deliberation::create(['inscription_id' => $inscription->id, 'annee_scolaire_id' => $s['annee']->id, 'decision' => 'admis']);

    $this->actingAs($s['admin'])->post(route('admin.deliberations.appliquer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'annee_cible_id' => $s['cible']->id,
    ])->assertRedirect();

    expect($eleve->fresh()->statut)->toBe('actif')
        ->and(Inscription::where('eleve_id', $eleve->id)->where('annee_scolaire_id', $s['cible']->id)->value('classe_id'))
        ->toBe($s['h1']->id);

    // Le certificat est réellement délivré (et tracé), puis l'élève passe de classe.
    $certification = Certification::where('eleve_id', $eleve->id)->first();

    expect($certification)->not->toBeNull()
        ->and($certification->libelle)->toBe("Certificat d'études primaires")
        ->and($certification->cycle_id)->toBe($s['primaire']);
});

test('un cycle déclaré diplôme fait sortir l’élève même si un cycle suit', function () {
    $s = scenarioCycles();
    DB::table('cycles')->where('id', $s['primaire'])->update(['fin_type' => 'diplome', 'fin_libelle' => 'Diplôme de fin de primaire']);

    $eleve = Eleve::create(['matricule' => 'ELV-21', 'nom' => 'Sortant', 'prenom' => 'Benoit', 'sexe' => 'M', 'statut' => 'actif']);
    $inscription = Inscription::create(['eleve_id' => $eleve->id, 'classe_id' => $s['p6']->id, 'annee_scolaire_id' => $s['annee']->id, 'date_inscription' => '2026-09-05', 'statut' => Inscription::STATUT_ACTIF]);
    Deliberation::create(['inscription_id' => $inscription->id, 'annee_scolaire_id' => $s['annee']->id, 'decision' => 'admis']);

    $this->actingAs($s['admin'])->post(route('admin.deliberations.appliquer'), [
        'annee_scolaire_id' => $s['annee']->id,
        'annee_cible_id' => $s['cible']->id,
    ])->assertRedirect()->assertSessionHas('success', fn ($m) => str_contains($m, '1 diplômé(s)'));

    expect($eleve->fresh()->statut)->toBe('diplome')
        ->and(Inscription::where('annee_scolaire_id', $s['cible']->id)->count())->toBe(0);
});

test('un cycle déclaré certificat n’affiche jamais diplômé, même sans cycle suivant', function () {
    $s = scenarioCycles();
    DB::table('cycles')->where('id', $s['primaire'])->update(['fin_type' => 'certificat', 'fin_libelle' => "Certificat d'études primaires"]);
    // Le cycle des humanités est désactivé : plus aucun cycle après le primaire.
    DB::table('cycles')->where('id', $s['humanites'])->update(['statut' => 'inactif']);

    $classe = $s['p6']->fresh();

    expect($classe->estTerminale())->toBeTrue()      // plus aucune classe d'accueil
        ->and($classe->donneDiplome())->toBeFalse();  // mais la fin déclarée = certificat
});
