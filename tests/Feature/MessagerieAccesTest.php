<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach ([
        'messages', 'conversation_user', 'conversations', 'notifications', 'professeurs',
        'parametres_systeme', 'users',
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
        $table->string('sexe');
        $table->string('statut')->default('actif');
        $table->timestamps();
    });
    Schema::create('parametres_systeme', function (Blueprint $table) {
        $table->id();
        $table->string('nom_etablissement', 150)->default('Etablissement');
        $table->string('nom_court', 60)->default('Ecole');
        $table->timestamps();
    });
    DB::table('parametres_systeme')->insert(['nom_court' => 'Ecole Test', 'created_at' => now(), 'updated_at' => now()]);

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
});

test('every role gets a messenger button on a page that does not offer it', function () {
    foreach (['professeur', 'comptable', 'prefetture', 'titulaire', 'admin'] as $role) {
        $compte = User::factory()->create(['role' => $role]);

        $html = $this->actingAs($compte)
            ->get(route('notifications.index'))
            ->assertOk()
            ->getContent();

        // Le bouton est injecté une seule fois, avec le lien vers la messagerie.
        expect(substr_count($html, '<a class="messagerie-flottante'))->toBe(1)
            ->and($html)->toContain(route('messagerie.index'))
            ->and($html)->toContain('aria-label="Messagerie"');
    }
});

test('the button carries the unread counter', function () {
    $alice = User::factory()->create(['role' => 'admin']);
    $bruno = User::factory()->create(['role' => 'comptable']);

    App\Support\Messagerie::creer($alice, [$bruno->id], 'Question', 'Bonjour Bruno.');

    $html = $this->actingAs($bruno)->get(route('notifications.index'))->assertOk()->getContent();

    expect($html)->toContain('data-non-lus')
        ->and(substr_count($html, '<a class="messagerie-flottante'))->toBe(1);

    // Une fois la messagerie consultée, le compteur disparaît du bouton.
    App\Support\Messagerie::marquerLu(App\Support\Messagerie::conversationsDe($bruno)->first(), $bruno);

    $html = $this->actingAs($bruno)->get(route('notifications.index'))->assertOk()->getContent();

    expect($html)->toContain('<a class="messagerie-flottante')
        ->not->toContain('data-non-lus');
});

test('no duplicate on a page that already offers the messenger', function () {
    $compte = User::factory()->create(['role' => 'comptable']);

    // La page /messagerie contient déjà ses propres liens : pas d'injection.
    $html = $this->actingAs($compte)->get(route('messagerie.index'))->assertOk()->getContent();

    expect($html)->not->toContain('<a class="messagerie-flottante');

    // Un visiteur non connecté n'obtient aucun bouton.
    $this->post(route('logout'));
    $this->get(route('login'))->assertOk()->assertDontSee('<a class="messagerie-flottante', false);
});
