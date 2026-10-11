<?php

use App\Models\User;
use App\Support\Messagerie;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Suppression « pour soi » : retirer une discussion de sa propre liste ne doit
 * jamais faire perdre les messages aux autres participants.
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
});

test('retirer une discussion ne l’enlève que d’un côté et garde les messages', function () {
    $alice = User::factory()->create(['role' => 'admin']);
    $bruno = User::factory()->create(['role' => 'professeur']);

    $conversation = Messagerie::creer($alice, [$bruno->id], 'Minerval', 'Bonjour Bruno.');

    $this->actingAs($bruno)
        ->delete(route('messagerie.supprimer', $conversation))
        ->assertRedirect();

    expect(Messagerie::conversationsDe($bruno->fresh()))->toHaveCount(0)   // retirée pour Bruno
        ->and(Messagerie::conversationsDe($alice->fresh()))->toHaveCount(1) // intacte pour Alice
        ->and($conversation->messages()->count())->toBe(1);                 // aucun message perdu

    // Alice retrouve bien le message dans son fil.
    $this->actingAs($alice)
        ->get(route('messagerie.index', ['conversation' => $conversation->id]))
        ->assertOk()
        ->assertSee('Bonjour Bruno');
});

test('un nouveau message fait réapparaître la discussion retirée', function () {
    $alice = User::factory()->create(['role' => 'admin']);
    $bruno = User::factory()->create(['role' => 'professeur']);

    $conversation = Messagerie::creer($alice, [$bruno->id], 'Sujet', 'Premier message.');

    $this->actingAs($bruno)->delete(route('messagerie.supprimer', $conversation))->assertRedirect();
    expect(Messagerie::conversationsDe($bruno->fresh()))->toHaveCount(0);

    // L'autre écrit : la discussion revient, rien n'est perdu.
    Messagerie::envoyer($conversation, $alice->fresh(), 'Un nouveau message.');

    expect(Messagerie::conversationsDe($bruno->fresh()))->toHaveCount(1)
        ->and($conversation->messages()->count())->toBe(2);
});

test('un non-participant ne peut pas retirer la discussion d’autrui', function () {
    $alice = User::factory()->create(['role' => 'admin']);
    $bruno = User::factory()->create(['role' => 'professeur']);
    $etranger = User::factory()->create(['role' => 'comptable']);

    $conversation = Messagerie::creer($alice, [$bruno->id], 'Privé', 'Coucou');

    $this->actingAs($etranger)
        ->delete(route('messagerie.supprimer', $conversation))
        ->assertForbidden();

    expect(Messagerie::conversationsDe($bruno->fresh()))->toHaveCount(1);
});

test('le parent retire sa discussion sans que le préfet perde les messages', function () {
    DB::table('parametres_systeme')->insert([
        'nom_court' => 'Ecole Test',
        'portail_parents_actif' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $parent = User::factory()->create(['role' => 'parent']);
    $prefet = User::factory()->create(['role' => 'prefetture']);

    $conversation = Messagerie::creer($prefet, [$parent->id], 'Minerval', 'Votre solde est de 20 $.');

    $this->actingAs($parent)
        ->delete(route('messagerie.supprimer', $conversation))
        ->assertRedirect();

    expect(Messagerie::conversationsDe($parent->fresh()))->toHaveCount(0)
        ->and(Messagerie::conversationsDe($prefet->fresh()))->toHaveCount(1);

    // Le portail du parent ne montre plus la discussion, le préfet garde tout.
    $this->actingAs($parent)
        ->get(route('parent.dashboard'))
        ->assertOk()
        ->assertDontSee('Votre solde est de 20 $.');

    $this->actingAs($prefet)
        ->get(route('messagerie.index', ['conversation' => $conversation->id]))
        ->assertOk()
        ->assertSee('Votre solde est de 20 $.');
});
