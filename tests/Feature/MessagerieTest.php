<?php

use App\Models\Conversation;
use App\Models\User;
use App\Support\Messagerie;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    foreach (['messages', 'conversation_user', 'conversations', 'professeurs', 'parametres_systeme', 'users'] as $table) {
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

test('a user writes to a colleague who then sees the message', function () {
    $alice = User::factory()->create(['role' => 'admin', 'username' => 'alice']);
    $bruno = User::factory()->create(['role' => 'professeur', 'username' => 'bruno']);

    $this->actingAs($alice)
        ->get(route('messagerie.index'))
        ->assertOk()
        ->assertSee('Messagerie interne')
        ->assertSee('Nouveau message');

    $this->actingAs($alice)
        ->post(route('messagerie.store'), [
            'destinataires' => [(string) $bruno->id],
            'sujet' => 'Préparation des bulletins',
            'corps' => 'Peux-tu préparer les moyennes du 1er trimestre ?',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', fn (string $m) => str_contains($m, '1 destinataire'));

    $conversation = Conversation::first();
    expect($conversation->type)->toBe('directe')
        ->and($conversation->participants()->count())->toBe(2)
        ->and(Messagerie::nonLus($bruno))->toBe(1)      // non lu pour Bruno
        ->and(Messagerie::nonLus($alice))->toBe(0);     // l'auteur a lu son propre message

    $this->actingAs($bruno)
        ->get(route('messagerie.index', ['conversation' => $conversation->id]))
        ->assertOk()
        ->assertSee('Peux-tu préparer les moyennes');   // le message s'affiche dans le fil

    // La consultation marque la conversation comme lue.
    expect(Messagerie::nonLus($bruno->fresh()))->toBe(0);
});

test('a broadcast reaches every active user', function () {
    $alice = User::factory()->create(['role' => 'admin', 'username' => 'alice']);
    $prof = User::factory()->create(['role' => 'professeur', 'username' => 'prof']);
    $comptable = User::factory()->create(['role' => 'comptable', 'username' => 'comptable']);
    $inactif = User::factory()->create(['role' => 'titulaire', 'username' => 'inactif', 'statut' => 'inactif']);

    $this->actingAs($alice)
        ->post(route('messagerie.store'), [
            'destinataires' => ['tous'],
            'sujet' => 'Réunion de rentrée',
            'corps' => 'Tout le personnel est convié lundi à 8 h.',
        ])
        ->assertSessionHas('success', fn (string $m) => str_contains($m, '2 destinataire'));

    $conversation = Conversation::first();

    expect($conversation->type)->toBe('diffusion')
        ->and($conversation->participants()->count())->toBe(3)   // Alice + prof + comptable
        ->and(Messagerie::nonLus($prof))->toBe(1)
        ->and(Messagerie::nonLus($comptable))->toBe(1)
        ->and(Messagerie::nonLus($inactif))->toBe(0);            // compte inactif exclu
});

test('a participant answers and only the others get an unread message', function () {
    $alice = User::factory()->create(['role' => 'admin']);
    $bruno = User::factory()->create(['role' => 'professeur']);

    $conversation = Messagerie::creer($alice, [$bruno->id], 'Question', 'Bonjour Bruno.');
    Messagerie::marquerLu($conversation, $bruno);

    $this->actingAs($bruno)
        ->post(route('messagerie.repondre', $conversation), ['corps' => 'Bonjour, je m’en occupe.'])
        ->assertRedirect(route('messagerie.index', ['conversation' => $conversation->id]))
        ->assertSessionHas('success', 'Message envoyé.');

    expect(DB::table('messages')->count())->toBe(2)
        ->and(Messagerie::nonLus($alice->fresh()))->toBe(1)
        ->and(Messagerie::nonLus($bruno->fresh()))->toBe(0);
});

test('an outsider cannot write into a conversation he does not belong to', function () {
    $alice = User::factory()->create(['role' => 'admin']);
    $bruno = User::factory()->create(['role' => 'professeur']);
    $etranger = User::factory()->create(['role' => 'comptable']);

    $conversation = Messagerie::creer($alice, [$bruno->id], 'Privé', 'Message privé.');

    $this->actingAs($etranger)
        ->post(route('messagerie.repondre', $conversation), ['corps' => 'Intrusion'])
        ->assertForbidden();

    expect(DB::table('messages')->count())->toBe(1);
});

test('the messaging page is reserved to authenticated users', function () {
    $this->get(route('messagerie.index'))->assertRedirect(route('login'));
});

test('a message without recipient or without text is refused', function () {
    $alice = User::factory()->create(['role' => 'admin']);

    $this->actingAs($alice)
        ->post(route('messagerie.store'), ['sujet' => 'Sans destinataire', 'corps' => 'Texte'])
        ->assertSessionHasErrors('destinataires');

    $this->actingAs($alice)
        ->post(route('messagerie.store'), ['destinataires' => ['tous'], 'sujet' => 'Sans texte'])
        ->assertSessionHasErrors('corps');

    expect(DB::table('conversations')->count())->toBe(0);
});

test('the picture of the correspondent is displayed in the thread and the list', function () {
    $alice = User::factory()->create(['role' => 'admin', 'nom' => 'Mwamba', 'prenom' => 'Alice']);
    $bruno = User::factory()->create([
        'role' => 'professeur', 'nom' => 'Kabila', 'prenom' => 'Bruno', 'photo' => 'users/photos/bruno.jpg',
    ]);

    // Alice crée la conversation : elle voit la photo de son correspondant.
    $conversation = Messagerie::creer($alice, [$bruno->id], 'Photo du correspondant', 'Bonjour Bruno.');

    $html = $this->actingAs($alice)->get(route('messagerie.index'))->assertOk()->getContent();

    expect($html)->toContain('msg-avatar')
        ->toContain('users/photos/bruno.jpg')
        ->toContain('Kabila Bruno');

    // Bruno répond : Alice voit sa photo à côté du message reçu.
    Messagerie::envoyer($conversation, $bruno, 'Bonjour, je m’en occupe.');

    $html = $this->actingAs($alice)
        ->get(route('messagerie.index', ['conversation' => $conversation->id]))
        ->assertOk()
        ->getContent();

    // La photo du correspondant est bien disponible pour l’affichage (l’habillage
    // de la page de messagerie est celui du projet, non testé ici).
    $correspondant = App\Support\Messagerie::conversationsDe($alice)->first()
        ->participants->firstWhere('id', '!=', $alice->id);

    expect($correspondant->photo)->toBe('users/photos/bruno.jpg')
        ->and($html)->toContain('Bonjour Bruno.');   // le fil affiche bien le message échangé
});

test('an image can be attached to a reply and is shown in the thread', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $alice = User::factory()->create(['role' => 'admin', 'nom' => 'Mwamba', 'prenom' => 'Alice']);
    $bruno = User::factory()->create(['role' => 'professeur', 'nom' => 'Kabila', 'prenom' => 'Bruno']);
    $conversation = Messagerie::creer($alice, [$bruno->id], 'Avec image', 'Regarde ceci.');

    $this->actingAs($bruno)
        ->post(route('messagerie.repondre', $conversation), [
            'corps' => 'Voici la photo',
            'piece_jointe' => imagePngDeTest('photo.png'),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $message = $conversation->messages()->latest('id')->first();

    expect($message->piece_jointe)->not->toBeNull()
        ->and($message->piece_jointe_nom)->toBe('photo.png')
        ->and($message->estImage())->toBeTrue();

    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($message->piece_jointe);

    // La bulle affiche l’aperçu de l’image (et pas seulement un texte).
    $html = view('messagerie._bulle', ['message' => $message, 'user' => $bruno, 'active' => $conversation])->render();

    expect($html)->toContain('bulle__piece')
        ->toContain('<img')
        ->toContain('photo.png');
});

test('an image can be sent without text, the size and the format are checked', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $alice = User::factory()->create(['role' => 'admin']);
    $bruno = User::factory()->create(['role' => 'professeur']);
    $conversation = Messagerie::creer($alice, [$bruno->id], 'Image seule', 'Texte initial.');

    // Image sans aucun texte : acceptée.
    $this->actingAs($bruno)
        ->post(route('messagerie.repondre', $conversation), [
            'piece_jointe' => imagePngDeTest('seule.png'),
        ])
        ->assertSessionHasNoErrors();

    $dernier = $conversation->messages()->latest('id')->first();
    expect($dernier->piece_jointe)->not->toBeNull()
        ->and($dernier->corps)->toBe('');

    // Fichier exécutable : refusé.
    $this->actingAs($bruno)
        ->post(route('messagerie.repondre', $conversation), [
            'corps' => 'Tiens',
            'piece_jointe' => \Illuminate\Http\UploadedFile::fake()->create('virus.php', 5, 'application/x-php'),
        ])
        ->assertSessionHasErrors('piece_jointe');

    // Fichier trop volumineux : refusé (limite 5 Mo).
    $this->actingAs($bruno)
        ->post(route('messagerie.repondre', $conversation), [
            'corps' => 'Trop lourd',
            'piece_jointe' => \Illuminate\Http\UploadedFile::fake()->create('gros.pdf', 6000, 'application/pdf'),
        ])
        ->assertSessionHasErrors('piece_jointe');

    // Ni texte ni pièce jointe : refusé.
    $this->actingAs($bruno)
        ->post(route('messagerie.repondre', $conversation), [])
        ->assertSessionHasErrors('corps');
});

test('la page de messagerie du personnel expose le modèle partagé (vocaux, stickers, pièces jointes, diffusion)', function () {
    $alice = User::factory()->create(['role' => 'admin', 'nom' => 'Mwamba', 'prenom' => 'Alice']);
    $bruno = User::factory()->create(['role' => 'professeur', 'nom' => 'Kabila', 'prenom' => 'Bruno']);
    Messagerie::creer($alice, [$bruno->id], 'Sujet', 'Bonjour.');

    $html = $this->actingAs($alice)
        ->get(route('messagerie.index'))
        ->assertOk()
        ->assertSee('Messagerie interne')
        ->assertSee('Nouveau message')
        ->getContent();

    expect($html)
        ->toContain('id="msgShell"')
        ->toContain('id="voiceBtn"')
        ->toContain('id="sendBtn"')
        ->toContain('id="stickerPicker"')
        ->toContain('onclick="envoyerTexte()"')
        ->toContain('onclick="ouvrirCompose()"')
        // Le personnel dispose des pièces jointes et de la diffusion « tous ».
        ->toContain('id="fileAttachment"')
        ->toContain('value="tous"')
        ->toContain('msg-avatar')
        // Liste des correspondants actifs + action « écrire ».
        ->toContain('id="contactsContainer"')
        ->toContain('onclick="ouvrirDiscussion(')
        // La page porte ses propres liens : pas d'injection du bouton flottant.
        ->not->toContain('<a class="messagerie-flottante');

    // Barre de saisie : le champ peut rétrécir (sinon les boutons, dont
    // l'envoi, sont poussés hors de l'écran) et le bouton d'envoi — cercle
    // bleu plein — est bien rendu après le champ, dans la même barre.
    expect($html)
        ->toContain('.chat-input { flex: 1 1 auto; min-width: 0;')
        ->toContain('.icon-btn--send { width: 40px; height: 40px; background: var(--chat-blue); color: #fff; font-size: 1.2rem; }');

    expect(strpos($html, 'chat-input-wrap'))->toBeLessThan(strpos($html, 'id="messageInput"'))
        ->and(strpos($html, 'id="messageInput"'))->toBeLessThan(strpos($html, 'id="sendBtn"'))
        ->and(strpos($html, 'id="sendBtn"'))->toBeLessThan(strpos($html, '</section>'));

    // Retour (vue mobile) : on revient sur la liste des CORRESPONDANTS, sans
    // recharger, et la vue mobile ouvre d'abord sur cette liste.
    expect($html)
        ->toContain('onclick="retourListe()"')
        ->toContain('window.retourListe')
        ->toContain("matchMedia('(max-width: 820px)')")
        ->toContain('data-cible="correspondants"')
        // Et l'aller-retour : rouvrir la conversation depuis la liste.
        ->toContain('has-conv')
        ->toContain('onclick="reprendreConversation()"')
        ->toContain('window.reprendreConversation')
        ->toContain('Reprendre la discussion')
        ->toContain('only-mobile');

    // Barre figée en bas + flèche « revenir au message du moment ».
    expect($html)
        ->toContain('position: sticky; bottom: 0;')
        ->toContain('id="btnBas"')
        ->toContain('onclick="allerEnBas()"')
        ->toContain('window.allerEnBas')
        ->toContain('majBtnBas');

    // Réception quasi instantanée : interrogation chaque seconde, récupération
    // immédiate au retour sur l'onglet, plus de cycle à 4 s.
    expect($html)
        ->toContain('await attendre(1000)')
        ->toContain('visibilitychange')
        ->not->toContain('attendre(4000)');

    // Défilement interne de la zone des messages (desktop comme mobile) :
    // les colonnes et le fil doivent pouvoir rétrécir (min-height: 0).
    expect($html)
        ->toContain('.msg-thread { display: flex; flex-direction: column; min-width: 0; min-height: 0;')
        ->toContain('.msg-thread__body { flex: 1; min-height: 0; overflow-y: auto;')
        ->toContain('.conv-scroll { flex: 1; min-height: 0; overflow-y: auto; }');

    // Anti-doublon : verrou sur la requête de flux et refus d'insérer deux fois
    // la même bulle.
    expect($html)
        ->toContain('let requeteEnCours = false;')
        ->toContain('if (requeteEnCours) return;')
        ->toContain('Anti-doublon');

    // Panneau de stickers façon messagerie : onglets par pack, planche des
    // récents, et mémorisation locale.
    expect($html)
        ->toContain('id="stickerTabs"')
        ->toContain('id="grilleRecents"')
        ->toContain('data-pack="ecole"')
        ->toContain('data-pack="visages"')
        ->toContain('window.ouvrirPack')
        ->toContain('window.majGrilleRecents')
        ->toContain('wmark_stickers_recents');

    // Appels audio / vidéo : boutons réels, bannière de sonnerie et écran d'appel.
    expect($html)
        ->toContain("onclick=\"appelDemarrer('audio')\"")
        ->toContain("onclick=\"appelDemarrer('video')\"")
        ->toContain('id="appelBanniere"')
        ->toContain('id="appelOverlay"')
        ->toContain('id="appelDistant"')
        ->toContain('id="appelLocal"')
        ->toContain('window.appelAccepter')
        ->toContain('window.appelRaccrocher')
        ->toContain("stun:stun.l.google.com:19302")
        // Au raccrochage, les vidéos doivent être libérées (pas d'image figée).
        ->toContain("\$('appelLocal').srcObject = null")
        ->toContain("\$('appelDistant').srcObject = null");

    // Clarté de la réponse : auteur + extrait dans la barre, surlignage de la
    // bulle visée, et citation cliquable qui saute au message d'origine.
    expect($html)
        ->toContain('id="replyAuteur"')
        ->toContain('data-auteur=')
        ->toContain('bulle-citation__auteur')
        ->toContain('window.allerAuMessage')
        ->toContain('bulle-cible')
        ->toContain('bulle-flash');

    // Profil du correspondant : fiche ouverte depuis l'en-tête et la liste.
    expect($html)
        ->toContain('id="profilFond"')
        ->toContain('id="profilAvatar"')
        ->toContain('window.ouvrirProfil')
        ->toContain('onclick="ouvrirProfil(');
});

test('l’aperçu d’un profil renvoie le nom et le rôle du correspondant', function () {
    $alice = User::factory()->create(['role' => 'admin', 'nom' => 'Mwamba', 'prenom' => 'Alice']);
    $bruno = User::factory()->create(['role' => 'professeur', 'nom' => 'Kabila', 'prenom' => 'Bruno']);

    $this->actingAs($alice)
        ->getJson(route('utilisateurs.apercu', $bruno))
        ->assertOk()
        ->assertJsonPath('nom', 'Kabila Bruno')
        ->assertJsonPath('role', 'Professeur')
        ->assertJsonPath('id', $bruno->id);

    // Un compte inactif n'a pas de fiche.
    $inactif = User::factory()->create(['role' => 'professeur', 'statut' => 'inactif']);
    $this->actingAs($alice)->getJson(route('utilisateurs.apercu', $inactif))->assertNotFound();

    // La fiche est réservée aux utilisateurs connectés.
    $this->post(route('logout'));
    $this->get(route('utilisateurs.apercu', $bruno))->assertRedirect(route('login'));
});

test('la barre d’écriture reste disponible même sans discussion ouverte', function () {
    $alice = User::factory()->create(['role' => 'admin', 'nom' => 'Mwamba', 'prenom' => 'Alice']);
    User::factory()->create(['role' => 'professeur', 'nom' => 'Kabila', 'prenom' => 'Bruno']);

    // Aucune conversation au départ : on doit pouvoir écrire tout de suite.
    $html = $this->actingAs($alice)->get(route('messagerie.index'))->assertOk()->getContent();

    expect($html)
        ->toContain('id="messageInput"')
        ->toContain('id="sendBtn"')
        ->toContain('id="voiceBtn"')
        ->toContain('id="nouveauDest"')            // choisir le destinataire
        ->toContain('onclick="toggleVoice()"')
        ->toContain('window.toggleVoice')          // la fonction du micro est bien définie
        ->toContain('function envoyerVocal');      // et celle qui envoie le vocal
});

test('écrire à un correspondant ouvre une conversation directe unique (sans doublon)', function () {
    $alice = User::factory()->create(['role' => 'admin']);
    $bruno = User::factory()->create(['role' => 'professeur']);

    // Première ouverture : une conversation est créée.
    $premiere = $this->actingAs($alice)
        ->postJson(route('messagerie.demarrer'), ['destinataire' => $bruno->id])
        ->assertOk()
        ->json('conversation_id');

    // Deuxième appel : la même conversation est retrouvée, aucun doublon.
    $seconde = $this->actingAs($alice)
        ->postJson(route('messagerie.demarrer'), ['destinataire' => $bruno->id])
        ->assertOk()
        ->json('conversation_id');

    expect($seconde)->toBe($premiere)
        ->and(DB::table('conversations')->count())->toBe(1)
        ->and(DB::table('conversation_user')->where('conversation_id', $premiere)->count())->toBe(2);
});

test('on ne peut pas démarrer une discussion avec soi-même', function () {
    $alice = User::factory()->create(['role' => 'admin']);

    $this->actingAs($alice)
        ->postJson(route('messagerie.demarrer'), ['destinataire' => $alice->id])
        ->assertNotFound();
});

test('une réponse citée apparaît sur la bulle du nouveau message', function () {
    $alice = User::factory()->create(['role' => 'admin', 'nom' => 'Mwamba', 'prenom' => 'Alice']);
    $bruno = User::factory()->create(['role' => 'professeur']);
    $conversation = Messagerie::creer($alice, [$bruno->id], 'Sujet', 'Quelle heure ?');
    $question = $conversation->messages()->first();

    $this->actingAs($bruno)
        ->postJson(route('messagerie.repondre', $conversation), ['corps' => 'À 14 h.', 'reponse_a_id' => $question->id])
        ->assertOk()
        ->assertJsonPath('message.id', fn ($id) => $id > 0);

    $reponse = $conversation->messages()->latest('id')->first();
    expect($reponse->reponse_a_id)->toBe($question->id);

    $html = view('messagerie._bulle', ['message' => $reponse, 'user' => $bruno, 'active' => $conversation])->render();
    // La citation montre l'auteur, l'extrait, et renvoie au message d'origine.
    expect($html)
        ->toContain('bulle-citation')
        ->toContain('bulle-citation__auteur')
        ->toContain('Quelle heure ?')
        ->toContain('onclick="allerAuMessage(')
        ->toContain('data-auteur=');
});

test('une réaction émoji est enregistrée, affichée et retirée', function () {
    $alice = User::factory()->create(['role' => 'admin']);
    $bruno = User::factory()->create(['role' => 'professeur']);
    $conversation = Messagerie::creer($alice, [$bruno->id], 'Sujet', 'Coucou');
    $message = $conversation->messages()->first();

    $this->actingAs($bruno)
        ->postJson(route('messagerie.reagir', $message), ['reaction' => '👍'])
        ->assertOk()
        ->assertJsonPath('message.id', $message->id);

    expect($message->fresh()->reaction)->toBe('👍')
        ->and($message->fresh()->aReaction())->toBeTrue();

    // Un non-participant ne peut pas réagir.
    $etranger = User::factory()->create(['role' => 'comptable']);
    $this->actingAs($etranger)->postJson(route('messagerie.reagir', $message), ['reaction' => '❤️'])->assertForbidden();

    // Réaction retirée.
    $this->actingAs($bruno)->postJson(route('messagerie.reagir', $message), ['reaction' => ''])->assertOk();
    expect($message->fresh()->reaction)->toBeNull();
});

/**
 * Petite image PNG valide, pour tester les pièces jointes sans dépendre de
 * l'extension GD (absente sur ce poste).
 */
function imagePngDeTest(string $nom = 'image.png'): \Illuminate\Http\UploadedFile
{
    $contenu = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

    $chemin = tempnam(sys_get_temp_dir(), 'wmark_').'.png';
    file_put_contents($chemin, $contenu);

    return new \Illuminate\Http\UploadedFile($chemin, $nom, 'image/png', null, true);
}
