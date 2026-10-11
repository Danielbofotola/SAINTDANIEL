<?php

use App\Models\Conversation;
use App\Models\User;
use App\Support\Messagerie;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach (['messages', 'conversation_user', 'conversations', 'notifications', 'professeurs', 'parametres_systeme', 'users'] as $table) {
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

test('l’administrateur voit toutes les conversations, même celles dont il est absent', function () {
    $admin = User::factory()->create(['role' => 'admin', 'nom' => 'Mwamba', 'prenom' => 'Alice']);
    $bruno = User::factory()->create(['role' => 'professeur', 'nom' => 'Kabila', 'prenom' => 'Bruno']);
    $chloe = User::factory()->create(['role' => 'comptable', 'nom' => 'Nsimba', 'prenom' => 'Chloé']);

    // L'admin n'est PAS participant de cette conversation.
    Messagerie::creer($bruno, [$chloe->id], 'Entre collègues', 'Bonjour Chloé.');

    $this->actingAs($admin)
        ->get(route('admin.messagerie.index'))
        ->assertOk()
        ->assertSee('Modération de la messagerie')
        ->assertSee('Entre collègues')
        ->assertSee('Kabila')
        ->assertSee('Nsimba');

    // La recherche filtre par participant.
    $this->actingAs($admin)->get(route('admin.messagerie.index', ['q' => 'Nsimba']))
        ->assertOk()->assertSee('Entre collègues');
    $this->actingAs($admin)->get(route('admin.messagerie.index', ['q' => 'zzz-inexistant']))
        ->assertOk()->assertSee('Aucune conversation');
});

test('l’administrateur peut lire le contenu d’une conversation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $bruno = User::factory()->create(['role' => 'professeur', 'nom' => 'Kabila', 'prenom' => 'Bruno']);
    $chloe = User::factory()->create(['role' => 'comptable', 'nom' => 'Nsimba', 'prenom' => 'Chloé']);

    $conversation = Messagerie::creer($bruno, [$chloe->id], 'Sujet privé', 'Contenu à surveiller.');
    Messagerie::envoyer($conversation, $chloe, 'Réponse de Chloé.');

    $this->actingAs($admin)
        ->get(route('admin.messagerie.show', $conversation))
        ->assertOk()
        ->assertSee('Sujet privé')
        ->assertSee('Contenu à surveiller.')
        ->assertSee('Réponse de Chloé.')
        ->assertSee('Participants');
});

test('l’administrateur peut supprimer une conversation et tous ses messages', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $bruno = User::factory()->create(['role' => 'professeur']);
    $chloe = User::factory()->create(['role' => 'comptable']);

    $conversation = Messagerie::creer($bruno, [$chloe->id], 'À supprimer', 'Un message.');
    Messagerie::envoyer($conversation, $chloe, 'Un autre.');
    $id = $conversation->id;

    $this->actingAs($admin)
        ->delete(route('admin.messagerie.destroy', $conversation))
        ->assertRedirect(route('admin.messagerie.index'))
        ->assertSessionHas('success', fn ($m) => str_contains($m, 'supprimée'));

    expect(Conversation::find($id))->toBeNull()
        ->and(DB::table('messages')->where('conversation_id', $id)->count())->toBe(0)
        ->and(DB::table('conversation_user')->where('conversation_id', $id)->count())->toBe(0);
});

test('l’administrateur peut supprimer un seul message', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $bruno = User::factory()->create(['role' => 'professeur']);
    $chloe = User::factory()->create(['role' => 'comptable']);

    $conversation = Messagerie::creer($bruno, [$chloe->id], 'Modération', 'Message à retirer.');
    $message = $conversation->messages()->first();

    $this->actingAs($admin)
        ->delete(route('admin.messagerie.messages.destroy', $message))
        ->assertRedirect(route('admin.messagerie.show', $conversation))
        ->assertSessionHas('success', 'Message supprimé.');

    expect(DB::table('messages')->where('id', $message->id)->count())->toBe(0);
});

test('la modération de la messagerie est réservée à l’administration', function () {
    $prof = User::factory()->create(['role' => 'professeur']);
    $bruno = User::factory()->create(['role' => 'professeur']);
    $chloe = User::factory()->create(['role' => 'comptable']);
    $conversation = Messagerie::creer($bruno, [$chloe->id], 'Privé', 'Coucou.');

    $this->actingAs($prof)->get(route('admin.messagerie.index'))->assertForbidden();
    $this->actingAs($prof)->get(route('admin.messagerie.show', $conversation))->assertForbidden();
    $this->actingAs($prof)->delete(route('admin.messagerie.destroy', $conversation))->assertForbidden();

    // La conversation est intacte.
    expect(Conversation::find($conversation->id))->not->toBeNull();
});
