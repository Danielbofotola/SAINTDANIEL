<?php

use App\Models\Appel;
use App\Models\User;
use App\Support\Messagerie;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach (['appels', 'messages', 'conversation_user', 'conversations', 'notifications', 'professeurs', 'parametres_systeme', 'users'] as $table) {
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
    Schema::create('appels', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('conversation_id');
        $table->unsignedBigInteger('appelant_id');
        $table->unsignedBigInteger('destinataire_id');
        $table->string('type', 10)->default('audio');
        $table->string('statut', 20)->default('sonnerie');
        $table->text('offre')->nullable();
        $table->text('reponse')->nullable();
        $table->text('ice_appelant')->nullable();
        $table->text('ice_destinataire')->nullable();
        $table->timestamp('repond_le')->nullable();
        $table->unsignedInteger('duree')->nullable();
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

/** Prépare un appelant, un destinataire et leur conversation directe. */
function deuxCorrespondants(): array
{
    $alice = User::factory()->create(['role' => 'admin', 'nom' => 'Mwamba', 'prenom' => 'Alice']);
    $bruno = User::factory()->create(['role' => 'professeur', 'nom' => 'Kabila', 'prenom' => 'Bruno']);
    $conversation = Messagerie::creer($alice, [$bruno->id], 'Discussion', 'Bonjour.');

    return [$alice, $bruno, $conversation];
}

test('un appel vocal sortant crée une sonnerie chez le correspondant', function () {
    [$alice, $bruno, $conversation] = deuxCorrespondants();

    $id = $this->actingAs($alice)
        ->postJson(route('appels.store'), [
            'conversation_id' => $conversation->id,
            'type' => 'audio',
            'offre' => json_encode(['type' => 'offer', 'sdp' => 'v=0…']),
        ])
        ->assertOk()
        ->json('id');

    expect(Appel::find($id)->statut)->toBe('sonnerie');

    // Le destinataire voit la sonnerie entrante, avec l'offre et le nom.
    $entrant = $this->actingAs($bruno)->getJson(route('appels.entrant'))->assertOk()->json('appel');
    expect($entrant['id'])->toBe($id)
        ->and($entrant['type'])->toBe('audio')
        ->and($entrant['appelant'])->toBe('Mwamba Alice')
        ->and($entrant['offre'])->toContain('v=0');

    // L'appelant, lui, n'a aucune sonnerie entrante.
    expect($this->actingAs($alice)->getJson(route('appels.entrant'))->json('appel'))->toBeNull();
});

test('le destinataire répond et l’appelant reçoit la réponse SDP', function () {
    [$alice, $bruno, $conversation] = deuxCorrespondants();

    $id = $this->actingAs($alice)->postJson(route('appels.store'), [
        'conversation_id' => $conversation->id, 'type' => 'video', 'offre' => 'OFFRE',
    ])->json('id');

    $this->actingAs($bruno)
        ->postJson(route('appels.reponse', $id), ['reponse' => 'REPONSE'])
        ->assertOk();

    $etat = $this->actingAs($alice)->getJson(route('appels.show', $id))->assertOk()->json();
    expect($etat['statut'])->toBe('accepte')
        ->and($etat['role'])->toBe('appelant')
        ->and($etat['reponse'])->toBe('REPONSE');

    // Le destinataire ne reçoit jamais la réponse (elle vient de lui).
    expect($this->actingAs($bruno)->getJson(route('appels.show', $id))->json('reponse'))->toBeNull();
});

test('les candidats ICE déposés par chacun sont lus par l’autre', function () {
    [$alice, $bruno, $conversation] = deuxCorrespondants();

    $id = $this->actingAs($alice)->postJson(route('appels.store'), [
        'conversation_id' => $conversation->id, 'type' => 'audio', 'offre' => 'O',
    ])->json('id');

    $this->actingAs($alice)->postJson(route('appels.ice', $id), ['candidat' => 'CAND-A'])->assertOk();
    $this->actingAs($bruno)->postJson(route('appels.ice', $id), ['candidat' => 'CAND-B'])->assertOk();

    expect($this->actingAs($alice)->getJson(route('appels.show', $id))->json('ice'))->toBe(['CAND-B'])
        ->and($this->actingAs($bruno)->getJson(route('appels.show', $id))->json('ice'))->toBe(['CAND-A']);
});

test('refuser et terminer changent l’état, visible des deux côtés', function () {
    [$alice, $bruno, $conversation] = deuxCorrespondants();

    $id = $this->actingAs($alice)->postJson(route('appels.store'), [
        'conversation_id' => $conversation->id, 'type' => 'audio', 'offre' => 'O',
    ])->json('id');

    $this->actingAs($bruno)->postJson(route('appels.refuser', $id))->assertOk();
    expect($this->actingAs($alice)->getJson(route('appels.show', $id))->json('statut'))->toBe('refuse');

    $second = $this->actingAs($alice)->postJson(route('appels.store'), [
        'conversation_id' => $conversation->id, 'type' => 'video', 'offre' => 'O',
    ])->json('id');

    $this->actingAs($bruno)->postJson(route('appels.terminer', $second))->assertOk();
    expect($this->actingAs($alice)->getJson(route('appels.show', $second))->json('statut'))->toBe('termine');
});

test('un étranger ne peut ni créer ni lire ni modifier un appel', function () {
    [$alice, $bruno, $conversation] = deuxCorrespondants();
    $etranger = User::factory()->create(['role' => 'comptable']);

    // Conversation dont il n'est pas participant : création refusée.
    $this->actingAs($etranger)->postJson(route('appels.store'), [
        'conversation_id' => $conversation->id, 'type' => 'audio', 'offre' => 'O',
    ])->assertForbidden();

    $id = $this->actingAs($alice)->postJson(route('appels.store'), [
        'conversation_id' => $conversation->id, 'type' => 'audio', 'offre' => 'O',
    ])->json('id');

    $this->actingAs($etranger)->getJson(route('appels.show', $id))->assertForbidden();
    $this->actingAs($etranger)->postJson(route('appels.terminer', $id))->assertForbidden();
    $this->actingAs($etranger)->postJson(route('appels.refuser', $id))->assertForbidden();
});

test('l’appel demande une conversation et un type valides', function () {
    [$alice, $bruno, $conversation] = deuxCorrespondants();

    $this->actingAs($alice)->postJson(route('appels.store'), ['type' => 'audio', 'offre' => 'O'])
        ->assertJsonValidationErrors('conversation_id');

    $this->actingAs($alice)->postJson(route('appels.store'), [
        'conversation_id' => $conversation->id, 'type' => 'hologramme', 'offre' => 'O',
    ])->assertJsonValidationErrors('type');

    $this->actingAs($alice)->postJson(route('appels.store'), [
        'conversation_id' => $conversation->id, 'type' => 'audio',
    ])->assertJsonValidationErrors('offre');
});

test('un appel laisse une trace dans le fil : sonnerie, puis manqué', function () {
[$alice, $bruno, $conversation] = deuxCorrespondants();

$id = $this->actingAs($alice)->postJson(route('appels.store'), [
    'conversation_id' => $conversation->id, 'type' => 'audio', 'offre' => 'O',
])->json('id');

// Un message-événement rejoint le fil de la conversation.
$trace = $conversation->messages()->whereNotNull('appel_id')->latest('id')->first();
expect($trace)->not->toBeNull()
    ->and($trace->appel_id)->toBe($id)
    ->and($trace->appel->libelleEtat())->toBe('sonnerie…');

// Passé une minute sans décrochage, l'appel devient « manqué ».
$trace->appel->forceFill(['created_at' => now()->subMinutes(2)])->save();
expect($trace->appel->fresh()->estManque())->toBeTrue()
    ->and($trace->appel->fresh()->libelleEtat())->toBe('manqué');

// La bulle d'appel l'affiche, avec un bouton pour rappeler.
$html = view('messagerie._bulle', ['message' => $trace->fresh(), 'user' => $bruno, 'active' => $conversation])->render();
expect($html)->toContain('bulle-appel')
    ->toContain('bulle-appel--rate')
    ->toContain('manqué')
    ->toContain('Appel vocal')
    ->toContain('Rappeler');
});

test('un appel décroché puis terminé mémorise sa durée', function () {
[$alice, $bruno, $conversation] = deuxCorrespondants();

$id = $this->actingAs($alice)->postJson(route('appels.store'), [
    'conversation_id' => $conversation->id, 'type' => 'video', 'offre' => 'O',
])->json('id');

$this->actingAs($bruno)->postJson(route('appels.reponse', $id), ['reponse' => 'R'])->assertOk();
expect(Appel::find($id)->repond_le)->not->toBeNull();

// 90 secondes de conversation, puis raccrochage.
Appel::find($id)->forceFill(['repond_le' => now()->subSeconds(90)])->save();
$this->actingAs($bruno)->postJson(route('appels.terminer', $id))->assertOk();

$appel = Appel::find($id);
expect($appel->statut)->toBe('termine')
    ->and($appel->duree)->toBeGreaterThanOrEqual(89)
    ->and($appel->libelleEtat())->toContain('terminé ·');
});
