<?php

use App\Models\Sanction;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Discipline — registre des sanctions du Préfet des Études.
 */
beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'sanctions', 'inscriptions', 'eleves', 'classes', 'cycles', 'annees_scolaires', 'users',
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
        $table->string('username')->unique();
        $table->string('email')->unique();
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
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('cycles', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('statut')->default('actif');
    });
    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
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
    Schema::create('sanctions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('classe_id')->nullable();
        $table->unsignedBigInteger('annee_scolaire_id');
        $table->string('type', 30);
        $table->text('motif');
        $table->date('date_sanction');
        $table->date('date_fin')->nullable();
        $table->text('observations')->nullable();
        $table->string('statut', 20)->default('en_cours');
        $table->unsignedBigInteger('prononce_par')->nullable();
        $table->timestamp('levee_le')->nullable();
        $table->timestamps();
    });

    DB::table('annees_scolaires')->insert(['id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01', 'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif']);
    DB::table('cycles')->insert(['id' => 1, 'nom' => 'Primaire', 'statut' => 'actif']);
    DB::table('classes')->insert(['id' => 1, 'nom' => '3e primaire', 'cycle_id' => 1, 'statut' => 'actif']);

    DB::table('eleves')->insert([
        ['id' => 1, 'matricule' => 'ELV-001', 'nom' => 'Banzolo', 'prenom' => 'Daniel', 'sexe' => 'M', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'matricule' => 'ELV-002', 'nom' => 'Inactif', 'prenom' => 'Zoé', 'sexe' => 'F', 'statut' => 'inactif', 'created_at' => now(), 'updated_at' => now()],
    ]);
    foreach ([1, 2] as $eleveId) {
        DB::table('inscriptions')->insert(['eleve_id' => $eleveId, 'classe_id' => 1, 'annee_scolaire_id' => 1, 'date_inscription' => '2026-09-05', 'statut' => 'actif', 'created_at' => now(), 'updated_at' => now()]);
    }
});

test('le préfet voit le registre et prononce une sanction', function () {
    $prefet = User::factory()->create(['role' => 'prefetture', 'nom' => 'Mwamba', 'prenom' => 'Alice']);

    $this->actingAs($prefet)->get(route('prefecture.discipline.index'))
        ->assertOk()
        ->assertSee('Registre des sanctions')
        ->assertSee('Banzolo')
        // Un élève désactivé n'est pas proposé au formulaire.
        ->assertDontSee('Inactif');

    $this->actingAs($prefet)->post(route('prefecture.discipline.store'), [
        'eleve_id' => 1,
        'type' => 'exclusion_temporaire',
        'motif' => 'Perturbation répétée du cours',
        'date_sanction' => '2026-11-10',
        'date_fin' => '2026-11-12',
    ])->assertRedirect()->assertSessionHas('success');

    $sanction = Sanction::firstOrFail();

    expect($sanction->eleve_id)->toBe(1)
        ->and($sanction->classe_id)->toBe(1)
        ->and($sanction->annee_scolaire_id)->toBe(1)
        ->and($sanction->type)->toBe('exclusion_temporaire')
        ->and($sanction->statut)->toBe('en_cours')
        ->and($sanction->prononce_par)->toBe($prefet->id);
});

test('une sanction peut être levée puis annulée', function () {
    $prefet = User::factory()->create(['role' => 'prefetture']);
    $sanction = Sanction::create([
        'eleve_id' => 1, 'classe_id' => 1, 'annee_scolaire_id' => 1,
        'type' => 'consigne', 'motif' => 'Retards répétés', 'date_sanction' => '2026-11-08',
        'statut' => 'en_cours', 'prononce_par' => $prefet->id,
    ]);

    $this->actingAs($prefet)->patch(route('prefecture.discipline.lever', $sanction))->assertRedirect();
    expect($sanction->fresh()->statut)->toBe('levee')
        ->and($sanction->fresh()->levee_le)->not->toBeNull();

    $this->actingAs($prefet)->delete(route('prefecture.discipline.destroy', $sanction))->assertRedirect();
    expect(Sanction::find($sanction->id))->toBeNull();
});

test('le registre disciplinaire est réservé au préfet et au poste disciplinaire', function () {
    $professeur = User::factory()->create(['role' => 'professeur']);

    $this->actingAs($professeur)->get(route('prefecture.discipline.index'))->assertForbidden();
});

test('le poste disciplinaire a accès au registre', function () {
    $disciplinaire = User::factory()->create(['role' => 'disciplinaire']);

    $this->actingAs($disciplinaire)->get(route('prefecture.discipline.index'))->assertOk();
});
