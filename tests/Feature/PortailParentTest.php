<?php

use App\Models\User;
use App\Support\Messagerie;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach (['notifications', 'messages', 'conversation_user', 'conversations', 'eleve_user', 'inscriptions', 'classes', 'annees_scolaires', 'eleves', 'parametres_systeme', 'professeurs', 'users'] as $table) {
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
        $table->boolean('saisie_notes_active')->default(true);
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

/** Ouvre (ou ferme) le module Portail Parents. */
function reglerPortailParents(bool $actif): void
{
    DB::table('parametres_systeme')->insert([
        'nom_court' => 'Ecole Test',
        'portail_parents_actif' => $actif,
        'saisie_notes_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('un parent accède à son espace quand le module est ouvert', function () {
    reglerPortailParents(true);

    $parent = User::factory()->create(['role' => 'parent', 'nom' => 'Bofotola', 'prenom' => 'Daniel']);
    User::factory()->create(['role' => 'professeur', 'nom' => 'Mapika', 'prenom' => 'Joseph']);
    $prefet = User::factory()->create(['role' => 'prefetture', 'nom' => 'Kalala', 'prenom' => 'Pierre']);

    Messagerie::creer($prefet, [$parent->id], 'Bienvenue', 'Bonjour, bienvenue sur le portail.');

    $this->actingAs($parent)
        ->get(route('parent.messages'))
        ->assertOk()
        ->assertSee('Espace Parent')
        ->assertSee('bienvenue sur le portail')
        ->assertSee('Kalala')
        // Les autres services (ici le professeur) restent invisibles.
        ->assertDontSee('Mapika')
        // L'interface partagée est bien câblée pour ce rôle.
        ->assertSee('id="msgShell"', false)
        ->assertSee('id="voiceBtn"', false)
        ->assertSee('id="sendBtn"', false)
        ->assertSee('id="stickerPicker"', false)
        ->assertSee('onclick="ouvrirCompose()"', false)
        ->assertSee('onclick="toggleStickers()"', false)
        ->assertSee('onclick="toggleVoice()"', false)
        ->assertSee('onclick="envoyerTexte()"', false)
        // Liste des correspondants actifs + action « écrire ».
        ->assertSee('id="contactsContainer"', false)
        ->assertSee('onclick="ouvrirDiscussion(', false)
        // Les parents n'ont pas le trombone ni la diffusion « tous ».
        ->assertDontSee('id="fileAttachment"', false)
        ->assertDontSee('value="tous"', false);
});

test('l’espace parent répond 404 quand le module est fermé', function () {
    reglerPortailParents(false);

    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)->get(route('parent.messages'))->assertNotFound();
});

test('l’espace parent est interdit aux autres rôles', function () {
    reglerPortailParents(true);

    $comptable = User::factory()->create(['role' => 'comptable']);
    $professeur = User::factory()->create(['role' => 'professeur']);

    $this->actingAs($comptable)->get(route('parent.messages'))->assertForbidden();
    $this->actingAs($professeur)->get(route('parent.messages'))->assertForbidden();
});

test('un administrateur connecté peut ouvrir le portail parent', function () {
    reglerPortailParents(true);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('parent.messages'))
        ->assertOk()
        ->assertSee('Espace Parent');
});

test('un admin qui consulte un compte parent accède au portail', function () {
    reglerPortailParents(true);

    $admin = User::factory()->create(['role' => 'admin']);
    $parent = User::factory()->create(['role' => 'parent']);

    // Session de consultation : l'admin s'est connecté au compte du parent.
    $this->actingAs($parent)
        ->withSession(['impersonating_admin_id' => $admin->id])
        ->get(route('parent.messages'))
        ->assertOk()
        ->assertSee('Espace Parent');
});

test('en consultation, une route strictement réservée à l’admin reste interdite', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $parent = User::factory()->create(['role' => 'parent']);

    $this->actingAs($parent)
        ->withSession(['impersonating_admin_id' => $admin->id])
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('le portail d’un parent ne propose que le préfet comme correspondant', function () {
    reglerPortailParents(true);

    $parent = User::factory()->create(['role' => 'parent']);
    $prefet = User::factory()->create(['role' => 'prefetture']);
    User::factory()->create(['role' => 'professeur']);
    User::factory()->create(['role' => 'comptable']);

    // La page se rend, et seuls les préfets sont proposés comme correspondants.
    $this->actingAs($parent)->get(route('parent.messages'))->assertOk()
        ->assertSee('Espace Parent');

    $correspondants = Messagerie::correspondantsQuery($parent)->get();

    expect($correspondants->pluck('id')->all())->toBe([$prefet->id])
        ->and($correspondants->pluck('role')->unique()->all())->toBe(['prefetture']);
});

test('le portail propose au parent de démarrer la discussion en un clic', function () {
    reglerPortailParents(true);

    $parent = User::factory()->create(['role' => 'parent']);
    $prefet = User::factory()->create(['role' => 'prefetture', 'nom' => 'Kalala', 'prenom' => 'Pierre']);

    // Sans discussion : raccourci direct vers le préfet.
    $this->actingAs($parent)
        ->get(route('parent.messages'))
        ->assertOk()
        ->assertSee('Démarrer la discussion')
        ->assertSee('Écrire à Kalala')
        ->assertSee('ouvrirDiscussion('.$prefet->id.')', false);

    // Le raccourci reste disponible et ouvre la discussion déjà existante.
    Messagerie::creer($prefet, [$parent->id], 'Bonjour', 'Bienvenue.');

    $this->actingAs($parent)
        ->get(route('parent.messages'))
        ->assertOk()
        ->assertSee('Écrire à Kalala')
        ->assertSee('Ouvrir la discussion')
        ->assertDontSee('Démarrer la discussion');
});

test('un parent écrit au préfet depuis le portail', function () {
    reglerPortailParents(true);

    $parent = User::factory()->create(['role' => 'parent']);
    $prefet = User::factory()->create(['role' => 'prefetture']);

    $this->actingAs($parent)
        ->post(route('messagerie.store'), [
            'destinataires' => [(string) $prefet->id],
            'sujet' => 'Question minerval',
            'corps' => 'Bonjour, quel est le solde restant ?',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', fn (string $m) => str_contains($m, '1 destinataire'));

    expect(DB::table('messages')->count())->toBe(1);
    expect(Messagerie::nonLus($prefet->fresh()))->toBe(1);
});

test('un parent ne peut pas écrire à un professeur (destinataire hors périmètre)', function () {
    reglerPortailParents(true);

    $parent = User::factory()->create(['role' => 'parent']);
    $prof = User::factory()->create(['role' => 'professeur']);

    $this->actingAs($parent)
        ->post(route('messagerie.store'), [
            'destinataires' => [(string) $prof->id],
            'sujet' => 'Bonjour',
            'corps' => 'Test',
        ])
        ->assertRedirect()
        ->assertSessionHas('error', fn (string $m) => str_contains($m, 'Aucun destinataire'));

    expect(DB::table('messages')->count())->toBe(0);
    expect(DB::table('conversations')->count())->toBe(0);
});

test('un parent ne peut pas démarrer une discussion avec un professeur', function () {
    $parent = User::factory()->create(['role' => 'parent']);
    $prof = User::factory()->create(['role' => 'professeur']);

    $this->actingAs($parent)
        ->post(route('messagerie.demarrer'), ['destinataire' => $prof->id])
        ->assertNotFound();
});

test('un parent ne voit ni ne lit les discussions avec les autres services', function () {
    reglerPortailParents(true);

    $parent = User::factory()->create(['role' => 'parent']);
    $prefet = User::factory()->create(['role' => 'prefetture']);
    $comptable = User::factory()->create(['role' => 'comptable']);

    $avecPrefet = Messagerie::creer($prefet, [$parent->id], 'Minerval', 'Bonjour.');
    Messagerie::creer($comptable, [$parent->id], 'Caisse', 'Bonjour.');

    $reponse = $this->actingAs($parent)->get(route('parent.messages'))->assertOk();

    $conversations = collect($reponse->viewData('conversations'));
    expect($conversations->pluck('id')->all())->toBe([$avecPrefet->id]);

    // Répondre dans une discussion masquée est refusé, même avec l'identifiant.
    $cachee = \App\Models\Conversation::query()
        ->whereHas('participants', fn ($query) => $query->where('user_id', $comptable->id))
        ->firstOrFail();

    $this->post(route('messagerie.repondre', $cachee), ['corps' => 'Réponse'])
        ->assertForbidden();
});

test('un message vocal est enregistré et rendu par un lecteur audio', function () {
    Storage::fake('public');
    reglerPortailParents(true);

    $parent = User::factory()->create(['role' => 'parent']);
    $prefet = User::factory()->create(['role' => 'prefetture']);
    $conversation = Messagerie::creer($prefet, [$parent->id], 'Vocal', 'Envoyez-moi un vocal.');

    $this->actingAs($parent)
        ->post(route('messagerie.repondre', $conversation), [
            'piece_jointe' => fichierAudioDeTest('vocal.wav'),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $message = $conversation->messages()->latest('id')->first();

    expect($message->piece_jointe)->not->toBeNull()
        ->and($message->estAudio())->toBeTrue();

    $html = view('messagerie._bulle', ['message' => $message, 'user' => $parent, 'active' => $conversation])->render();

    expect($html)->toContain('bulle__audio')->toContain('<audio');
});

test('les stickers (emojis seuls) reçoivent un habillage dédié', function () {
    $parent = User::factory()->create(['role' => 'parent']);
    $prof = User::factory()->create(['role' => 'professeur']);
    $conversation = Messagerie::creer($prof, [$parent->id], 'Réaction', 'Merci !');

    Messagerie::envoyer($conversation, $parent, '👍');
    $message = $conversation->messages()->latest('id')->first();

    expect($message->estSticker())->toBeTrue();

    $html = view('messagerie._bulle', ['message' => $message, 'user' => $parent, 'active' => $conversation])->render();
    expect($html)->toContain('bulle--sticker');
});

test('chaque rubrique de l’espace parent a sa propre page', function () {
    reglerPortailParents(true);

    $parent = User::factory()->create(['role' => 'parent']);

    // L'accueil sert les annonces et ne charge pas la messagerie (plus d'embouteillage).
    $this->actingAs($parent)->get(route('parent.dashboard'))->assertOk()
        ->assertSee('Espace Parent')
        ->assertSee('Communications de l')
        ->assertDontSee('id="msgShell"', false);

    foreach (['parent.notifications', 'parent.messages', 'parent.enfants', 'parent.profil'] as $route) {
        $this->actingAs($parent)->get(route($route))->assertOk()->assertSee('Espace Parent');
    }
});

test('le parent ouvre le détail d’une notification, qui passe en lue', function () {
    reglerPortailParents(true);

    $parent = User::factory()->create(['role' => 'parent']);
    $id = (string) \Illuminate\Support\Str::uuid();

    DB::table('notifications')->insert([
        'id' => $id,
        'type' => 'App\\Notifications\\AnnonceNotification',
        'notifiable_type' => $parent->getMorphClass(),
        'notifiable_id' => $parent->id,
        'data' => json_encode([
            'titre' => 'Réunion des parents',
            'message' => 'Réunion samedi à 9h.',
            'niveau' => 'info',
            'auteur' => 'Administration',
            'lien' => '/espace-parent',
        ]),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($parent->fresh()->unreadNotifications()->count())->toBe(1);

    // La liste renvoie vers le détail.
    $this->actingAs($parent)->get(route('parent.notifications'))->assertOk()
        ->assertSee('Réunion des parents')
        ->assertSee(route('parent.notification', $id), false);

    // Le détail montre tout le message et marque la notification comme lue.
    $this->actingAs($parent)->get(route('parent.notification', $id))->assertOk()
        ->assertSee('Réunion des parents')
        ->assertSee('Réunion samedi à 9h.')
        ->assertSee('Retour aux notifications');

    expect($parent->fresh()->unreadNotifications()->count())->toBe(0)
        ->and(DB::table('notifications')->where('id', $id)->value('read_at'))->not->toBeNull();

    // Un parent ne peut pas ouvrir la notification d'un autre compte.
    $autre = User::factory()->create(['role' => 'parent']);
    $this->actingAs($autre)->get(route('parent.notification', $id))->assertNotFound();
});

/**
 * Petit fichier audio WAVE valide, pour tester les pièces jointes vocales sans
 * dépendre d'un encodeur : la règle « mimes » détecte le type par le contenu.
 */
function fichierAudioDeTest(string $nom = 'vocal.wav'): \Illuminate\Http\UploadedFile
{
    $entete = 'RIFF'.pack('V', 36).'WAVEfmt '.pack('V', 16).pack('v', 1).pack('v', 1)
        .pack('V', 8000).pack('V', 8000).pack('v', 1).pack('v', 8).'data'.pack('V', 0);

    $chemin = tempnam(sys_get_temp_dir(), 'wmark_').'.wav';
    file_put_contents($chemin, $entete);

    return new \Illuminate\Http\UploadedFile($chemin, $nom, 'audio/wav', null, true);
}
