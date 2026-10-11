<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach (['journal_decisions', 'parametres_systeme', 'annees_scolaires', 'users'] as $table) {
        Schema::dropIfExists($table);
    }
    Schema::enableForeignKeyConstraints();

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('professeur_id')->nullable()->unique();
        $table->string('nom', 50)->nullable();
        $table->string('postnom', 50)->nullable();
        $table->string('prenom', 50)->nullable();
        $table->string('telephone', 20)->nullable();
        $table->text('adresse')->nullable();
        $table->string('photo')->nullable();
        $table->string('username')->unique();
        $table->string('email')->unique();
        $table->string('password');
        $table->string('pin_code')->nullable();
        $table->string('role');
        $table->string('statut');
        $table->dateTime('last_login')->nullable();
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
    Schema::create('journal_decisions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('annee_scolaire_id')->nullable();
        $table->unsignedBigInteger('auteur_id')->nullable();
        $table->string('type')->default('decision');
        $table->string('objet', 160);
        $table->text('corps');
        $table->string('destinataires', 255)->nullable();
        $table->date('echeance')->nullable();
        $table->dateTime('publie_le')->nullable();
        $table->timestamps();
    });
    Schema::create('parametres_systeme', function (Blueprint $table) {
        $table->id();
        $table->string('nom_court', 60)->nullable();
        $table->boolean('saisie_notes_active')->default(true);
        $table->timestamps();
    });

    DB::table('annees_scolaires')->insert([
        'id' => 1, 'libelle' => '2026-2027', 'date_debut' => '2026-09-01',
        'date_fin' => '2027-07-01', 'is_encours' => true, 'statut' => 'actif',
    ]);
    DB::table('parametres_systeme')->insert(['nom_court' => 'Ecole', 'saisie_notes_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
});

test('le préfet publie une décision ou une injonction tracée au journal', function () {
    $prefet = User::factory()->create(['role' => 'prefetture']);

    $this->actingAs($prefet)
        ->post(route('prefecture.decisions.store'), [
            'type' => 'injonction',
            'objet' => 'Fermer la saisie des notes',
            'corps' => 'L’encodage des points est fermé dès vendredi.',
            'destinataires' => 'Enseignants',
        ])
        ->assertRedirect();

    $decision = DB::table('journal_decisions')->first();
    expect($decision->type)->toBe('injonction')
        ->and($decision->objet)->toBe('Fermer la saisie des notes')
        ->and((int) $decision->auteur_id)->toBe($prefet->id)
        ->and((int) $decision->annee_scolaire_id)->toBe(1)
        ->and($decision->publie_le)->not->toBeNull();

    $this->actingAs($prefet)
        ->get(route('prefecture.decisions.index'))
        ->assertOk()
        ->assertSee('Fermer la saisie des notes');
});

test('le journal d’audit est réservé au Système et à l’Administrateur', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.audit.index'))
        ->assertOk()
        ->assertSee("Journal d'audit", false);

    $this->actingAs(User::factory()->create(['role' => 'prefetture']))
        ->get(route('admin.audit.index'))
        ->assertForbidden();
});

test('le préfet ouvre et ferme le flux académique', function () {
    $prefet = User::factory()->create(['role' => 'prefetture']);

    $this->actingAs($prefet)
        ->get(route('prefecture.flux.index'))
        ->assertOk()
        ->assertSee('Encodage des points');

    $this->actingAs($prefet)
        ->put(route('prefecture.flux.update'), ['saisie_notes_active' => 0])
        ->assertRedirect();

    expect((int) DB::table('parametres_systeme')->value('saisie_notes_active'))->toBe(0);
});

test('le poste Disciplinaire dispose de son espace de travail', function () {
    $this->actingAs(User::factory()->create(['role' => 'disciplinaire']))
        ->get(route('poste.show'))
        ->assertOk()
        ->assertSee('Poste de travail')
        ->assertSee('Assiduité');
});
