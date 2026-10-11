<?php

use App\Models\Eleve;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Configuration administrateur : rattacher un compte parent à son ou ses
 * enfant(s), et afficher ces enfants dans l'espace parent.
 */
beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach (['notifications', 'annonces', 'messages', 'conversation_user', 'conversations', 'eleve_user', 'inscriptions', 'classes', 'annees_scolaires', 'eleves', 'parametres_systeme', 'professeurs', 'users'] as $table) {
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
        $table->date('date_naissance')->nullable();
        $table->string('telephone', 20)->nullable();
        $table->string('adresse', 100)->nullable();
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
        $table->string('sexe');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('eleves', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 50);
        $table->string('postnom', 50)->nullable();
        $table->string('prenom', 50);
        $table->string('matricule')->nullable();
        $table->string('photo')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('eleve_user', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('user_id');
        $table->timestamps();
        $table->unique(['eleve_id', 'user_id']);
    });

    Schema::create('annees_scolaires', function (Blueprint $table) {
        $table->id();
        $table->string('libelle', 50);
        $table->boolean('is_encours')->default(false);
        $table->timestamps();
    });

    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->string('nom', 100);
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('inscriptions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('eleve_id');
        $table->unsignedBigInteger('classe_id')->nullable();
        $table->unsignedBigInteger('annee_scolaire_id')->nullable();
        $table->date('date_inscription')->nullable();
        $table->string('statut')->default('actif');
        $table->timestamps();
    });

    Schema::create('parametres_systeme', function (Blueprint $table) {
        $table->id();
        $table->string('nom_etablissement', 150)->default('Etablissement');
        $table->string('nom_court', 60)->default('Ecole');
        $table->boolean('portail_parents_actif')->default(false);
        $table->timestamps();
    });

    Schema::create('conversations', function (Blueprint $table) {
        $table->id();
        $table->string('sujet', 150);
        $table->string('type', 20)->default('directe');
        $table->unsignedBigInteger('createur_id')->nullable();
        $table->timestamp('derniere_activite_at')->nullable();
        $table->timestamps();
    });

    Schema::create('conversation_user', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('conversation_id');
        $table->unsignedBigInteger('user_id');
        $table->timestamp('dernier_lu_at')->nullable();
        $table->unsignedBigInteger('dernier_message_id')->nullable();
        $table->timestamp('supprime_at')->nullable();
        $table->timestamps();
        $table->unique(['conversation_id', 'user_id']);
    });

    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('conversation_id');
        $table->unsignedBigInteger('auteur_id')->nullable();
        $table->text('corps');
        $table->string('piece_jointe')->nullable();
        $table->string('piece_jointe_nom')->nullable();
        $table->unsignedBigInteger('reponse_a_id')->nullable();
        $table->string('reaction', 16)->nullable();
        $table->unsignedBigInteger('appel_id')->nullable();
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

    Schema::create('annonces', function (Blueprint $table) {
        $table->id();
        $table->string('titre', 150);
        $table->text('message');
        $table->string('niveau', 20)->default('info');
        $table->string('lien', 255)->nullable();
        $table->string('cible', 30)->default('tous');
        $table->unsignedBigInteger('envoye_par')->nullable();
        $table->unsignedInteger('destinataires')->default(0);
        $table->timestamps();
    });
});

test('l’administrateur rattache un ou plusieurs enfants à un compte parent', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $parent = User::factory()->create(['role' => 'parent', 'username' => 'papa', 'email' => 'papa@test.local']);

    $aicha = Eleve::create(['nom' => 'Bofotola', 'postnom' => 'M.', 'prenom' => 'Aicha', 'matricule' => 'ELV-1']);
    $kofi = Eleve::create(['nom' => 'Bofotola', 'postnom' => 'M.', 'prenom' => 'Kofi', 'matricule' => 'ELV-2']);

    $this->actingAs($admin)
        ->put(route('admin.profile.accounts.update', $parent), [
            'nom' => 'Bofotola',
            'prenom' => 'Daniel',
            'username' => 'papa',
            'email' => 'papa@test.local',
            'role' => 'parent',
            'statut' => 'actif',
            'enfants' => [$aicha->id, $kofi->id],
        ])
        ->assertRedirect(route('admin.profile.accounts'));

    expect($parent->fresh()->eleves()->pluck('eleves.id')->sort()->values()->all())
        ->toBe(collect([$aicha->id, $kofi->id])->sort()->values()->all())
        ->and(DB::table('eleve_user')->count())->toBe(2);
});

test('les enfants ne restent pas rattachés à un compte qui n’est pas parent', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $compte = User::factory()->create(['role' => 'parent', 'username' => 'compte', 'email' => 'compte@test.local']);
    $eleve = Eleve::create(['nom' => 'X', 'prenom' => 'Y', 'matricule' => 'ELV-9']);

    $compte->eleves()->sync([$eleve->id]);
    expect($compte->fresh()->eleves()->count())->toBe(1);

    $this->actingAs($admin)
        ->put(route('admin.profile.accounts.update', $compte), [
            'username' => 'compte',
            'email' => 'compte@test.local',
            'role' => 'comptable',
            'statut' => 'actif',
            'enfants' => [$eleve->id],
        ])
        ->assertRedirect(route('admin.profile.accounts'));

    expect($compte->fresh()->eleves()->count())->toBe(0);
});

test('le portail du parent affiche ses enfants', function () {
    DB::table('parametres_systeme')->insert([
        'nom_court' => 'Ecole Test',
        'portail_parents_actif' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $parent = User::factory()->create(['role' => 'parent']);
    $aicha = Eleve::create(['nom' => 'Bofotola', 'postnom' => 'M.', 'prenom' => 'Aicha', 'matricule' => 'ELV-1']);
    $parent->eleves()->sync([$aicha->id]);

    $this->actingAs($parent)
        ->get(route('parent.enfants'))
        ->assertOk()
        ->assertSee('Mes enfants')
        ->assertSee('Aicha')
        ->assertSee('ELV-1');
});

test('un parent sans enfant rattaché voit un message d’information', function () {
    DB::table('parametres_systeme')->insert([
        'nom_court' => 'Ecole Test',
        'portail_parents_actif' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)
        ->get(route('parent.enfants'))
        ->assertOk()
        ->assertSee('Aucun enfant n’est encore rattaché');
});
