@php
    /**
     * Interface de messagerie partagée par tous les espaces, au modèle
     * « Facebook Lite » : liste de conversations, chat plein écran, réponses
     * citées, réactions émoji et pouce d'envoi rapide.
     *
     * Variables attendues :
     *  - $user, $conversations, $active, $messages, $destinataires, $nonLus
     *  - $espaceUrl      : URL de la page hôte (rechargée avec ?conversation=ID)
     *  - $avecFichier    : afficher le trombone (pièces jointes)
     *  - $composeMultiple: autoriser plusieurs destinataires + diffusion « tous »
     */
    $avecFichier = $avecFichier ?? false;
    $composeMultiple = $composeMultiple ?? false;
    // Sans discussion en cours : on ouvre sur la liste des correspondants.
    $vueParDefaut = $conversations->isEmpty() ? 'correspondants' : 'discussions';

    // Packs de stickers (comme les autres messageries) : des planches d'emojis
    // rangées par thème, plus une planche « Récents » alimentée côté client.
    $packsStickers = [
        'ecole' => ['nom' => 'École', 'icone' => '📚', 'emojis' => ['📚', '🎓', '📝', '✏️', '📌', '🏫', '✅', '💯', '🧮', '📖', '📎', '🗓️', '🖍️', '🔔', '🧑‍🏫', '🎒']],
        'reactions' => ['nom' => 'Réactions', 'icone' => '👍', 'emojis' => ['👍', '👏', '🙏', '🤝', '💪', '🤗', '🫡', '✌️', '👌', '🤙', '🙌', '👋', '🫶', '🎉', '💐', '🔥']],
        'visages' => ['nom' => 'Visages', 'icone' => '😀', 'emojis' => ['😀', '😂', '🥳', '😍', '🤩', '😎', '🤔', '😅', '😉', '😴', '🥰', '😇', '🤯', '😢', '😡', '🤣']],
        'amour' => ['nom' => 'Amour', 'icone' => '❤️', 'emojis' => ['❤️', '💙', '💚', '💛', '💜', '🖤', '💖', '💞', '💕', '💘', '🌹', '💐', '😘', '🥰', '💋', '✨']],
        'fetes' => ['nom' => 'Fêtes', 'icone' => '🎉', 'emojis' => ['🎉', '🎊', '🎂', '🎁', '🎈', '🥳', '✨', '⭐', '🌟', '💫', '🏆', '🥇', '🎵', '🎶', '🕯️', '🎄']],
    ];

    $nomConversation = function ($conversation) use ($user) {
        $correspondant = $conversation->participants->firstWhere('id', '!=', $user->id);
        if ($conversation->type === 'directe' && $correspondant) {
            return trim(($correspondant->nom ?? '').' '.($correspondant->prenom ?? '')) ?: $correspondant->username;
        }
        return $conversation->sujet;
    };
    $initiales = fn ($compte) => mb_strtoupper(mb_substr($compte->prenom ?: $compte->username, 0, 1));
@endphp

@once
<style>
    :root {
        --chat-bg: #18191a; --chat-card: #242526; --chat-hover: #3a3b3c;
        --chat-text: #e4e6eb; --chat-muted: #b0b3b8; --chat-blue: #0084ff;
        --chat-divider: #2f3031; --chat-sent: #0084ff; --chat-received: #3e4042;
    }
    .msg-shell { display: grid; grid-template-columns: 360px minmax(0, 1fr); gap: 0; height: 76vh; min-height: 480px;
        background: var(--chat-bg); border: 1px solid var(--chat-divider); border-radius: 12px; overflow: hidden; }

    /* ===== Colonne liste (façon liste de discussions Facebook Lite) ===== */
    /* min-height:0 : sans ça, un enfant de grille/flex refuse de rétrécir et
       déborde -> c'est la PAGE qui défile au lieu de la zone des messages. */
    .msg-list { display: flex; flex-direction: column; min-width: 0; min-height: 0; border-right: 1px solid var(--chat-divider); }
    .msg-list__title { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px 4px; }
    .msg-list__title h2 { margin: 0; color: #fff; font-size: 1.35rem; font-weight: 800; }
    .msg-list__head { padding: 8px 12px 12px; display: grid; gap: 8px; }
    .msg-list__head input { width: 100%; background: #3a3b3c; border: 1px solid transparent; color: #fff;
        border-radius: 20px; padding: 8px 14px; font-size: .85rem; outline: none; }
    .btn-compose { background: var(--chat-blue); border: none; color: #fff; border-radius: 20px; padding: 8px;
        font-weight: 700; font-size: .85rem; cursor: pointer; width: 100%; }
    .btn-compose:hover { background: #2b8cff; }
    .msg-tabs { display: flex; gap: 6px; }
    .msg-tab { flex: 1; background: transparent; border: 1px solid var(--chat-divider); color: var(--chat-muted);
        border-radius: 20px; padding: 6px 8px; font-size: .78rem; font-weight: 700; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
    .msg-tab.active { background: var(--chat-blue); border-color: var(--chat-blue); color: #fff; }
    .conv-scroll { flex: 1; min-height: 0; overflow-y: auto; }
    .conv { display: flex; align-items: center; gap: 12px; padding: 10px 14px; cursor: pointer; color: var(--chat-text); }
    .conv:hover { background: var(--chat-hover); }
    .conv.active { background: var(--chat-hover); }
    .conv__name { color: #fff; font-weight: 700; font-size: .95rem; }
    .conv__meta { color: var(--chat-muted); font-size: .78rem; }
    .conv__meta--fort { color: #fff; font-weight: 700; }
    .conv__badge { background: #e41e3f; color: #fff; border-radius: 999px; font-size: .7rem; font-weight: 700; padding: 1px 7px; }
    .conv__vu { color: var(--chat-blue); font-size: .95rem; flex: none; }
    .conv__del { color: var(--chat-muted); font-size: .85rem; flex: none; padding: 4px 6px; border-radius: 6px; opacity: .6; }
    .conv__del:hover { color: #e41e3f; background: var(--chat-hover); opacity: 1; }
    .msg-avatar { width: 52px; height: 52px; border-radius: 50%; background: var(--chat-blue); color: #fff;
        display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem;
        object-fit: cover; flex: none; overflow: hidden; }
    .msg-avatar--sm { width: 34px; height: 34px; font-size: .8rem; }

    /* ===== Colonne discussion ===== */
    .msg-thread { display: flex; flex-direction: column; min-width: 0; min-height: 0; position: relative; }
    .msg-thread__head { display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 10px 14px; border-bottom: 1px solid var(--chat-divider); }
    .msg-thread__head .titre { color: #fff; font-weight: 700; font-size: .95rem; }
    .msg-thread__head .sous { color: var(--chat-muted); font-size: .74rem; }
    .msg-thread__icones { display: flex; gap: 16px; color: var(--chat-blue); font-size: 1.1rem; }
    .msg-thread__icones button { background: transparent; border: none; color: var(--chat-blue);
        cursor: pointer; font-size: 1.1rem; padding: 0; }
    .msg-thread__icones button:hover { color: #fff; }
    .msg-thread__body { flex: 1; min-height: 0; overflow-y: auto; padding: 14px; display: flex; flex-direction: column; gap: 10px; }
    .msg-thread__empty { margin: auto; text-align: center; color: var(--chat-muted); }
    .nouveau-dest { margin-top: 14px; display: inline-flex; flex-direction: column; gap: 6px; align-items: flex-start; }
    .nouveau-dest label { color: var(--chat-muted); font-size: .78rem; }
    .nouveau-dest select { background: #3a3b3c; border: none; color: #fff; border-radius: 10px; padding: 9px 12px; min-width: 240px; max-width: 100%; }

    /* sticky : la barre de saisie reste figée en bas, même quand on fait défiler. */
    .chat-input-wrap { padding: 10px 12px; border-top: 1px solid var(--chat-divider); background: var(--chat-bg);
        display: flex; align-items: center; gap: 10px; position: sticky; bottom: 0; z-index: 5; }
    /* Flèche « revenir au message du moment », affichée quand on remonte le fil. */
    .btn-bas { display: none; position: absolute; left: 50%; bottom: 74px; transform: translateX(-50%);
        width: 40px; height: 40px; border-radius: 50%; background: var(--chat-card); color: #fff;
        border: 1px solid var(--chat-divider); cursor: pointer; z-index: 6; box-shadow: 0 4px 14px rgba(0,0,0,.5);
        align-items: center; justify-content: center; }
    .btn-bas.on { display: inline-flex; }
    /* min-width:0 : sinon l'input garde sa largeur intrinsèque et pousse les
       boutons (dont l'envoi) hors de l'écran sur les petits écrans. */
    .chat-input { flex: 1 1 auto; min-width: 0; background: #3a3b3c; border: none;
        border-radius: 20px; padding: 9px 16px; color: #fff; outline: none; }
    .icon-btn { background: transparent; border: none; color: var(--chat-blue); width: 36px; height: 36px;
        border-radius: 50%; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
        flex: 0 0 auto; font-size: 1.05rem; }
    .icon-btn--send { width: 40px; height: 40px; background: var(--chat-blue); color: #fff; font-size: 1.2rem; }
    .icon-btn--rec { color: #e41e3f; animation: recpulse 1s infinite; }
    @keyframes recpulse { 50% { opacity: .5; } }
    .rec-status { display: none; align-items: center; gap: 8px; flex: 1; color: #ff8ea0; font-weight: 700; font-size: .85rem; }
    .rec-status.on { display: flex; }
    .rec-dot { width: 11px; height: 11px; border-radius: 50%; background: #e41e3f; animation: recpulse 1s infinite; }

    /* Barre de réponse citée */
    .reply-bar { display: flex; align-items: center; justify-content: space-between; gap: 10px;
        margin: 0 12px; padding: 8px 12px; border-left: 3px solid var(--chat-blue); background: var(--chat-card); border-radius: 8px; }
    .reply-bar__label { color: var(--chat-blue); font-size: .74rem; font-weight: 700; }
    .reply-bar__extrait { color: var(--chat-muted); font-size: .8rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .reply-bar__close { background: transparent; border: none; color: var(--chat-muted); cursor: pointer; }
    .reply-bar__auteur { color: #fff; }

    /* Panneau de stickers : onglets par pack, planche scrollable, récents. */
    .sticker-picker { display: none; position: absolute; bottom: 62px; left: 10px; right: 10px; max-height: 320px;
        background: var(--chat-card); border: 1px solid var(--chat-divider); border-radius: 14px; overflow: hidden;
        box-shadow: 0 -6px 22px rgba(0,0,0,.5); z-index: 100; flex-direction: column; }
    .sticker-picker.on { display: flex; }
    .sticker-picker__head { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px;
        border-bottom: 1px solid var(--chat-divider); color: var(--chat-muted); font-size: .78rem; font-weight: 700; }
    .sticker-tabs { display: flex; gap: 4px; padding: 8px 10px; overflow-x: auto; border-bottom: 1px solid var(--chat-divider); }
    .sticker-tab { background: transparent; border: none; cursor: pointer; font-size: 1.15rem; line-height: 1;
        padding: 5px 8px; border-radius: 8px; opacity: .6; flex: none; }
    .sticker-tab:hover { background: var(--chat-hover); opacity: 1; }
    .sticker-tab.active { background: var(--chat-hover); opacity: 1; box-shadow: inset 0 -2px 0 var(--chat-blue); }
    .sticker-grids { overflow-y: auto; padding: 10px; }
    .sticker-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 6px; text-align: center; font-size: 1.5rem; }
    .sticker-grid span { cursor: pointer; padding: 4px; border-radius: 8px; }
    .sticker-grid span:hover { background: var(--chat-hover); transform: scale(1.15); }
    .sticker-vide { color: var(--chat-muted); font-size: .78rem; margin: 0; padding: 6px; }

    .reaction-pop { display: none; position: fixed; z-index: 1100; background: var(--chat-card); border: 1px solid var(--chat-divider);
        border-radius: 999px; padding: 6px 10px; gap: 6px; box-shadow: 0 6px 20px rgba(0,0,0,.5); }
    .reaction-pop.on { display: flex; }
    .reaction-pop span { cursor: pointer; font-size: 1.3rem; }
    .reaction-pop span:hover { transform: scale(1.2); }

    /* ===== Bulles (partial _bulle) ===== */
    .msg-thread__body .bulle-container { display: flex; align-items: flex-end; gap: 8px; max-width: 84%; }
    .msg-thread__body .bulle-container--moi { align-self: flex-end; flex-direction: row-reverse; }
    .msg-thread__body .bulle-container--autre { align-self: flex-start; }
    .msg-thread__body .bulle-colonne { display: flex; flex-direction: column; min-width: 0; }
    .msg-thread__body .bulle-container--moi .bulle-colonne { align-items: flex-end; }
    .msg-thread__body .bulle-auteur { color: var(--chat-muted); font-size: .7rem; margin: 0 0 2px 6px; }
    /* Citation : auteur + extrait, bordure bleue, cliquable pour revenir au message. */
    .msg-thread__body .bulle-citation { background: #2d2e30; border-left: 3px solid var(--chat-blue); border-radius: 8px;
        font-size: .78rem; margin-bottom: -6px; padding: 6px 10px 8px; opacity: .95; display: flex; flex-direction: column;
        gap: 2px; max-width: 100%; cursor: pointer; }
    .msg-thread__body .bulle-citation__auteur { color: var(--chat-blue); font-weight: 700; font-size: .72rem;
        display: inline-flex; align-items: center; gap: 5px; }
    .msg-thread__body .bulle-citation__texte { color: #d0d2d6; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    /* Bulle visée par la réponse en cours de rédaction. */
    .msg-thread__body .bulle-cible .bulle { outline: 2px solid var(--chat-blue); outline-offset: 2px; }
    /* Éclat bref quand on saute à un message cité. */
    .msg-thread__body .bulle-flash .bulle { animation: bulleFlash 1.3s ease-out; }
    @keyframes bulleFlash { 0%, 45% { outline: 3px solid var(--chat-blue); outline-offset: 2px; } 100% { outline: 0 solid transparent; } }
    /* Événement d'appel dans le fil : sonnerie, manqué, refusé, terminé. */
    .msg-thread__body .bulle-appel { display: flex; align-items: center; gap: 10px; align-self: center; max-width: 92%;
        background: var(--chat-card); border: 1px solid var(--chat-divider); border-radius: 12px; padding: 8px 12px; }
    .msg-thread__body .bulle-appel__rond { width: 34px; height: 34px; border-radius: 50%; background: #2f6f4f; color: #d1fae5;
        display: inline-flex; align-items: center; justify-content: center; flex: none; }
    .msg-thread__body .bulle-appel__texte { display: flex; flex-direction: column; min-width: 0; }
    .msg-thread__body .bulle-appel__titre { color: #fff; font-weight: 700; font-size: .82rem; }
    .msg-thread__body .bulle-appel__etat { color: var(--chat-muted); font-weight: 600; }
    .msg-thread__body .bulle-appel__heure { color: var(--chat-muted); font-size: .68rem; }
    .msg-thread__body .bulle-appel__rappel { margin-left: auto; background: transparent; border: 1px solid var(--chat-divider);
        color: var(--chat-blue); border-radius: 999px; padding: 4px 10px; font-size: .72rem; font-weight: 700; cursor: pointer; flex: none; }
    .msg-thread__body .bulle-appel__rappel:hover { background: var(--chat-hover); }
    /* Appel manqué ou refusé : signalé en rouge. */
    .msg-thread__body .bulle-appel--rate .bulle-appel__rond { background: #7f1d1d; color: #fee2e2; }
    .msg-thread__body .bulle-appel--rate .bulle-appel__etat { color: #fca5a5; }
    .msg-thread__body .bulle { padding: 9px 14px; border-radius: 18px; font-size: .9rem; line-height: 1.35;
        word-break: break-word; position: relative; }
    .msg-thread__body .bulle--moi { background: var(--chat-sent); color: #fff; border-bottom-right-radius: 4px; }
    .msg-thread__body .bulle--autre { background: var(--chat-received); color: var(--chat-text); border-bottom-left-radius: 4px; }
    .msg-thread__body .bulle-reaction { background: var(--chat-card); border: 1px solid var(--chat-divider); border-radius: 999px;
        padding: 1px 6px; font-size: .8rem; margin-top: -8px; align-self: flex-start; }
    .msg-thread__body .bulle-pied { display: flex; align-items: center; gap: 8px; margin-top: 2px; }
    .msg-thread__body .bulle__meta { display: block; font-size: .62rem; color: var(--chat-muted); }
    .msg-thread__body .bulle-action { background: transparent; border: none; color: var(--chat-muted); cursor: pointer; font-size: .74rem; padding: 0; }
    .msg-thread__body .bulle-action:hover { color: #fff; }
    .msg-thread__body .msg-avatar { width: 30px; height: 30px; font-size: .72rem; }

    /* ===== Nouveau message ===== */
    .compose { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 1080; align-items: center; justify-content: center; padding: 16px; }
    .compose.on { display: flex; }
    .compose__box { background: var(--chat-card); border: 1px solid var(--chat-divider); border-radius: 14px; width: 100%; max-width: 560px; padding: 18px; }
    .compose__box h3 { margin: 0 0 14px; font-size: 1.05rem; color: #fff; }
    .compose label { display: block; font-size: .78rem; color: var(--chat-muted); margin-bottom: 5px; }
    .compose input, .compose select, .compose textarea { width: 100%; background: var(--chat-bg); border: 1px solid var(--chat-divider);
        color: #fff; border-radius: 8px; padding: 9px 12px; font-size: .88rem; outline: none; margin-bottom: 12px; }
    .compose textarea { resize: vertical; min-height: 84px; }
    .compose select[multiple] { min-height: 130px; }
    .compose__actions { display: flex; justify-content: flex-end; gap: 8px; }
    .compose__actions .btn-compose { width: auto; padding: 8px 18px; }
    .btn-ghost-2 { background: transparent; border: 1px solid var(--chat-divider); color: var(--chat-muted); border-radius: 8px; padding: 8px 16px; cursor: pointer; }

    /* ===== Mobile : liste ↔ conversation ===== */
    .only-mobile { display: none; }
    @media (max-width: 820px) {
        .msg-shell { grid-template-columns: 1fr; height: auto; min-height: 0; }
        .msg-list { border-right: none; height: 58vh; }
        .msg-thread { min-height: 0; }
        /* Un fil ouvert : plein écran, la liste s'efface. */
        .msg-shell.show-thread .msg-list { display: none; }
        .msg-shell.show-thread .msg-thread { min-height: 78vh; }
        /* Conversation chargée mais revenu à la liste : le fil s'efface,
           on le rouvre par « Reprendre la discussion ». */
        .msg-shell.has-conv:not(.show-thread) .msg-thread { display: none; }
        /* Sans conversation : la liste ET la barre d'écriture restent visibles. */
        .only-mobile { display: inline-flex !important; }
        /* Conversation ouverte : hauteur bornée -> le fil défile en interne et
           la barre de saisie reste figée en bas de l'écran. */
        .msg-shell.has-conv.show-thread { height: 82vh; }
        .msg-shell.has-conv.show-thread .msg-thread { height: 82vh; min-height: 0; }
    }
</style>
@endonce

<div class="msg-shell {{ $active ? 'show-thread has-conv' : '' }}" id="msgShell">
    <aside class="msg-list">
        <div class="msg-list__title">
            <h2>Messages</h2>
            <span style="color:var(--chat-muted)"><i class="fa-solid fa-magnifying-glass"></i></span>
        </div>
        <div class="msg-list__head">
            <div class="msg-tabs">
                <button type="button" class="msg-tab {{ $vueParDefaut === 'correspondants' ? 'active' : '' }}"
                        data-cible="correspondants" onclick="msgTab('correspondants', this)">
                    <i class="fa-solid fa-user-group"></i> Correspondants
                </button>
                <button type="button" class="msg-tab {{ $vueParDefaut === 'discussions' ? 'active' : '' }}"
                        data-cible="discussions" onclick="msgTab('discussions', this)">
                    <i class="fa-solid fa-comments"></i> Discussions
                </button>
            </div>
            @if ($active)
                <button class="btn-compose only-mobile" type="button" onclick="reprendreConversation()">
                    <i class="fa-solid fa-comment-dots"></i> Reprendre la discussion
                </button>
            @endif
            <button class="btn-compose" type="button" onclick="ouvrirCompose()">
                <i class="fa-solid fa-pen-to-square"></i> Nouveau message
            </button>
            <input type="text" id="convSearch" placeholder="Rechercher…">
        </div>

        <div class="conv-scroll" id="convContainer" data-vue="discussions" @if ($vueParDefaut !== 'discussions') style="display:none" @endif>
            @forelse ($conversations as $conversation)
                @php($corr = $conversation->participants->firstWhere('id', '!=', $user->id))
                <a class="conv {{ $active && $active->id === $conversation->id ? 'active' : '' }}"
                   href="{{ $espaceUrl.'?conversation='.$conversation->id }}"
                   @if ($active && $active->id === $conversation->id) onclick="reprendreConversation(); return false;" @endif
                   data-search="{{ mb_strtolower($nomConversation($conversation)) }}">
                    <span class="msg-avatar">
                        @if ($conversation->type !== 'directe' || ! $corr)
                            <i class="fa-solid fa-bullhorn"></i>
                        @elseif ($corr->photo)
                            <img class="msg-avatar" src="{{ asset('storage/'.$corr->photo) }}" alt="{{ $nomConversation($conversation) }}">
                        @else
                            {{ $initiales($corr) }}
                        @endif
                    </span>
                    <div style="flex:1; min-width:0">
                        <div class="conv__name text-truncate">{{ $nomConversation($conversation) }}</div>
                        <div class="text-truncate {{ $conversation->non_lus > 0 ? 'conv__meta--fort' : 'conv__meta' }}">
                            @if ($conversation->non_lus > 0)
                                <span class="conv__badge">{{ $conversation->non_lus }}</span>
                            @else
                                Vous :
                            @endif
                            {{ $conversation->dernierMessage?->corps }}
                        </div>
                    </div>
                    @if ($conversation->non_lus === 0)
                        <i class="fa-solid fa-circle-check conv__vu"></i>
                    @endif
                    <span class="conv__del" role="button" tabindex="0" title="Retirer de ma liste"
                          data-url="{{ route('messagerie.supprimer', $conversation) }}">
                        <i class="fa-solid fa-trash"></i>
                    </span>
                </a>
            @empty
                <p class="p-3 text-center mb-0" style="color:var(--chat-muted);font-size:.8rem">Aucune discussion.</p>
            @endforelse
        </div>

        <div class="conv-scroll" id="contactsContainer" data-vue="correspondants" @if ($vueParDefaut !== 'correspondants') style="display:none" @endif>
            @forelse ($destinataires as $compte)
                @php($nomCompte = trim(($compte->nom ?? '').' '.($compte->prenom ?? '')) ?: $compte->username)
                <div class="conv" data-search="{{ mb_strtolower($nomCompte.' '.\App\Models\User::libelleRole($compte->role)) }}"
                     onclick="ouvrirDiscussion({{ $compte->id }})">
                    <span class="msg-avatar" title="Voir le profil"
                          onclick="event.stopPropagation(); ouvrirProfil({{ $compte->id }})">
                        @if ($compte->photo)
                            <img class="msg-avatar" src="{{ asset('storage/'.$compte->photo) }}" alt="{{ $nomCompte }}">
                        @else
                            {{ $initiales($compte) }}
                        @endif
                    </span>
                    <div style="flex:1; min-width:0">
                        <div class="conv__name text-truncate">{{ $nomCompte }}</div>
                        <div class="conv__meta">{{ \App\Models\User::libelleRole($compte->role) }}</div>
                    </div>
                    <i class="fa-solid fa-comment-dots conv__vu"></i>
                </div>
            @empty
                <p class="p-3 text-center mb-0" style="color:var(--chat-muted);font-size:.8rem">Aucun correspondant actif.</p>
            @endforelse
        </div>
    </aside>

    <section class="msg-thread">
        @if ($active)
            <div class="msg-thread__head">
                <div class="d-flex align-items-center gap-2" style="min-width:0">
                    <button type="button" class="icon-btn d-inline-flex d-md-none" onclick="retourListe()" title="Retour à la liste">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    @php($corrActif = $active->participants->firstWhere('id', '!=', $user->id))
                    <span class="msg-avatar msg-avatar--sm" title="Voir le profil"
                          style="cursor:pointer" onclick="ouvrirProfil({{ $corrActif?->id }})">
                        @if ($active->type !== 'directe' || ! $corrActif)
                            <i class="fa-solid fa-bullhorn"></i>
                        @elseif ($corrActif->photo)
                            <img class="msg-avatar msg-avatar--sm" src="{{ asset('storage/'.$corrActif->photo) }}" alt="">
                        @else
                            {{ $initiales($corrActif) }}
                        @endif
                    </span>
                    <div style="min-width:0; cursor:pointer" onclick="ouvrirProfil({{ $corrActif?->id }})">
                        <div class="titre text-truncate">{{ $nomConversation($active) }}</div>
                        <div class="sous text-truncate">{{ $active->libelleType() }} · {{ $active->sujet }}</div>
                    </div>
                </div>
                <div class="msg-thread__icones">
                    <button type="button" onclick="appelDemarrer('audio')" title="Appel vocal"><i class="fa-solid fa-phone"></i></button>
                    <button type="button" onclick="appelDemarrer('video')" title="Appel vidéo"><i class="fa-solid fa-video"></i></button>
                    <i class="fa-solid fa-gear" title="Options"></i>
                </div>
            </div>

            <div class="msg-thread__body" id="messagesContainer" data-flux="{{ route('messagerie.messages', $active) }}">
                @foreach ($messages as $message)
                    @include('messagerie._bulle', ['message' => $message, 'active' => $active])
                @endforeach
            </div>

            <button class="btn-bas" id="btnBas" type="button" onclick="allerEnBas()" title="Revenir au plus récent">
                <i class="fa-solid fa-arrow-down"></i>
            </button>

            <div class="reply-bar" id="replyBar" style="display:none">
                <div style="min-width:0">
                    <div class="reply-bar__label"><i class="fa-solid fa-reply"></i> Réponse à <span class="reply-bar__auteur" id="replyAuteur"></span></div>
                    <div class="reply-bar__extrait" id="replyExtrait"></div>
                </div>
                <button type="button" class="reply-bar__close" onclick="annulerReponse()"><i class="fa-solid fa-xmark"></i></button>
            </div>

        @else
            <div class="msg-thread__empty">
                <i class="fa-solid fa-comment-dots fa-3x mb-3"></i>
                <p class="mb-0">Choisissez un correspondant, ou écrivez directement ci-dessous.</p>
                <div class="nouveau-dest">
                    <label for="nouveauDest">Nouveau message à</label>
                    <select id="nouveauDest">
                        @foreach ($destinataires as $compte)
                            @php($nomCompte = trim(($compte->nom ?? '').' '.($compte->prenom ?? '')) ?: $compte->username)
                            <option value="{{ $compte->id }}">{{ $nomCompte }} — {{ \App\Models\User::libelleRole($compte->role) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

            <div class="chat-input-wrap">
                <div class="sticker-picker" id="stickerPicker">
                    <div class="sticker-picker__head">
                        <span>Stickers</span>
                        <i class="fa-solid fa-xmark" style="cursor:pointer" onclick="toggleStickers()"></i>
                    </div>
                    <div class="sticker-tabs" id="stickerTabs">
                        <button type="button" class="sticker-tab active" data-pack="recents" onclick="ouvrirPack('recents', this)" title="Récents">🕘</button>
                        @foreach ($packsStickers as $cle => $pack)
                            <button type="button" class="sticker-tab" data-pack="{{ $cle }}" onclick="ouvrirPack('{{ $cle }}', this)" title="{{ $pack['nom'] }}">{{ $pack['icone'] }}</button>
                        @endforeach
                    </div>
                    <div class="sticker-grids" id="stickerGrids">
                        <div class="sticker-grid" data-pack="recents" id="grilleRecents"></div>
                        @foreach ($packsStickers as $cle => $pack)
                            <div class="sticker-grid" data-pack="{{ $cle }}" style="display:none">
                                @foreach ($pack['emojis'] as $sticker)
                                    <span onclick="envoyerSticker('{{ $sticker }}')">{{ $sticker }}</span>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($avecFichier)
                    <label class="icon-btn" for="fileAttachment" title="Joindre un fichier (5 Mo max)" style="cursor:pointer">
                        <i class="fa-solid fa-paperclip"></i>
                    </label>
                    <input type="file" id="fileAttachment" style="display:none"
                           accept="image/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip">
                @endif

                <button class="icon-btn" id="voiceBtn" type="button" onclick="toggleVoice()" title="Message vocal">
                    <i class="fa-solid fa-microphone"></i>
                </button>

                <input type="text" class="chat-input" id="messageInput" maxlength="2000"
                       placeholder="Message" autocomplete="off">

                <div class="rec-status" id="recStatus">
                    <span class="rec-dot"></span>
                    <span>Enregistrement… <span id="recTimer">0:00</span></span>
                </div>

                <button class="icon-btn" type="button" onclick="toggleStickers()" title="Stickers"><i class="fa-regular fa-face-smile"></i></button>
                <button class="icon-btn icon-btn--send" id="sendBtn" type="button" onclick="envoyerTexte()" title="Envoyer">
                    <i class="fa-solid fa-thumbs-up"></i>
                </button>
            </div>
    </section>
</div>

<div class="reaction-pop" id="reactionPop">
    @foreach (['👍','❤️','😂','😮','😢','🙏'] as $reaction)
        <span data-emoji="{{ $reaction }}">{{ $reaction }}</span>
    @endforeach
</div>

<div class="compose" id="composeModal">
    <div class="compose__box">
        <h3><i class="fa-solid fa-pen-to-square"></i> Nouveau message</h3>
        <label for="composeDest">Destinataire{{ $composeMultiple ? 's' : '' }}</label>
        <select id="composeDest" {{ $composeMultiple ? 'multiple' : '' }} @unless ($composeMultiple) required @endunless>
            @if ($composeMultiple)
                <option value="tous">📢 — Tous les utilisateurs ({{ $destinataires->count() + 1 }}) —</option>
            @endif
            @foreach ($destinataires as $compte)
                @php($nomCompte = trim(($compte->nom ?? '').' '.($compte->prenom ?? '')) ?: $compte->username)
                <option value="{{ $compte->id }}">{{ $nomCompte }} — {{ \App\Models\User::libelleRole($compte->role) }}</option>
            @endforeach
        </select>
        <label for="composeSujet">Objet</label>
        <input type="text" id="composeSujet" maxlength="150" placeholder="Ex. Réunion des parents">
        <label for="composeCorps">Message</label>
        <textarea id="composeCorps" maxlength="2000" placeholder="Votre message…"></textarea>
        <div class="compose__actions">
            <button class="btn-ghost-2" type="button" onclick="fermerCompose()">Annuler</button>
            <button class="btn-compose" type="button" id="composeSend"><i class="fa-solid fa-paper-plane"></i> Envoyer</button>
        </div>
    </div>
</div>

@once
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const FLUX_URL = @json($active ? route('messagerie.messages', $active) : null);
    const REPONDRE_URL = @json($active ? route('messagerie.repondre', $active) : null);
    const STORE_URL = @json(route('messagerie.store'));
    const DEMARRER_URL = @json(route('messagerie.demarrer'));
    const REAGIR_BASE = @json(route('messagerie.reagir', ['message' => '__ID__']));
    const ESPACE_URL = @json($espaceUrl);
    const AVEC_FICHIER = @json($avecFichier);
    const COMPOSE_MULTIPLE = @json($composeMultiple);

    let reponseAId = null;

    /* ---------- Fil temps réel ---------- */
    (function () {
        const fil = document.getElementById('messagesContainer');
        if (!fil) return;

        const input = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const fichier = document.getElementById('fileAttachment');
        let dernierId = 0;

        const bulles = fil.querySelectorAll('[data-message-id]');
        if (bulles.length) dernierId = Number(bulles[bulles.length - 1].getAttribute('data-message-id')) || 0;

        const bas = () => { fil.scrollTop = fil.scrollHeight; };
        bas();

        // Flèche « revenir au message du moment » : visible quand on a remonté le fil.
        const btnBas = document.getElementById('btnBas');
        const majBtnBas = () => {
            if (!btnBas) return;
            const loin = fil.scrollHeight - fil.scrollTop - fil.clientHeight > 120;
            btnBas.classList.toggle('on', loin);
        };
        fil.addEventListener('scroll', majBtnBas);
        majBtnBas();

        const ajouter = (html, id) => {
            if (!html) return;
            // Anti-doublon : la même bulle (même identifiant) n'est jamais affichée
            // deux fois, même si deux requêtes la rapportent (ex. envoi + flux).
            if (id && fil.querySelector('[data-message-id="' + id + '"]')) {
                dernierId = Math.max(dernierId, Number(id));
                return;
            }
            fil.insertAdjacentHTML('beforeend', html);
            if (id) dernierId = Math.max(dernierId, Number(id));
            bas();
            majBtnBas();
        };

        const majBadges = (total) => {
            document.querySelectorAll('[data-non-lus]').forEach(el => {
                if (total > 0) { el.textContent = total > 99 ? '99+' : total; el.style.display = ''; }
                else { el.style.display = 'none'; }
            });
        };

        window.envoyerTexte = async function (texteForce) {
            const texte = texteForce !== undefined ? texteForce : (input ? input.value.trim() : '');
            const joint = AVEC_FICHIER && fichier && fichier.files.length ? fichier.files[0] : null;
            if (!texte && !joint) return;

            // Aucun fil ouvert : on démarre une conversation avec le destinataire choisi.
            if (!REPONDRE_URL) {
                const sel = document.getElementById('nouveauDest');
                const dest = sel ? sel.value : null;
                if (!dest) { alert('Choisissez d’abord un destinataire.'); return; }
                sendBtn.disabled = true;
                try {
                    const donnees = new FormData();
                    donnees.append('_token', CSRF);
                    donnees.append('sujet', 'Discussion');
                    if (texte) donnees.append('corps', texte);
                    if (joint) donnees.append('piece_jointe', joint);
                    donnees.append('destinataires[]', dest);
                    const rep = await fetch(STORE_URL, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: donnees,
                        credentials: 'same-origin',
                    });
                    const json = await rep.json();
                    if (!rep.ok) throw json;
                    window.location.assign(ESPACE_URL + '?conversation=' + json.conversation_id);
                } catch (e) {
                    alert('Envoi impossible. Vérifiez le destinataire.');
                } finally {
                    sendBtn.disabled = false;
                }
                return;
            }

            sendBtn.disabled = true;
            try {
                const donnees = new FormData();
                donnees.append('_token', CSRF);
                if (texte) donnees.append('corps', texte);
                if (joint) donnees.append('piece_jointe', joint);
                if (reponseAId) donnees.append('reponse_a_id', reponseAId);
                const rep = await fetch(REPONDRE_URL, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: donnees,
                    credentials: 'same-origin',
                });
                const json = await rep.json();
                if (!rep.ok) throw json;
                ajouter(json.message.html, json.message.id);
                majBadges(json.non_lus);
                if (input) input.value = '';
                if (fichier) fichier.value = '';
                window.annulerReponse();
            } catch (e) {
                alert((e && (e.message || (e.errors && e.errors.piece_jointe && e.errors.piece_jointe[0]))) || 'Envoi impossible.');
            } finally {
                sendBtn.disabled = false;
                if (input) input.focus();
            }
        };

        if (input) {
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); window.envoyerTexte(); }
            });
        }

        if (FLUX_URL) {
            // Réception quasi instantanée : on interroge le fil chaque seconde
            // (au lieu de 4 s) et immédiatement dès que l'onglet redevient actif.
            const attendre = ms => new Promise(r => setTimeout(r, ms));
            // Verrou : une seule requête de flux à la fois. Sans lui, le cycle
            // et les gestionnaires focus/visibilitychange partent avec le même
            // « après » et rapportent deux fois le même message (doublon).
            let requeteEnCours = false;
            const recuperer = async () => {
                if (requeteEnCours) return;
                requeteEnCours = true;
                try {
                    const rep = await fetch(FLUX_URL + '?apres=' + dernierId, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    if (!rep.ok) return;
                    const json = await rep.json();
                    (json.messages || []).forEach(m => ajouter(m.html, m.id));
                    majBadges(json.non_lus);
                } catch (e) { /* réessai au tour suivant */ }
                finally { requeteEnCours = false; }
            };

            (async function boucle() {
                while (true) {
                    await attendre(1000);
                    if (document.hidden) continue;
                    await recuperer();
                }
            })();

            window.addEventListener('focus', recuperer);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) recuperer(); });
        }
    })();

    window.toggleStickers = function () {
        document.getElementById('stickerPicker').classList.toggle('on');
    };

    window.envoyerSticker = function (sticker) {
        window.toggleStickers();
        window.ajouterStickerRecent(sticker);
        window.envoyerTexte(sticker);
    };

    /* ---------- Stickers : onglets par pack + récents mémorisés ---------- */
    const CLE_RECENTS = 'wmark_stickers_recents';

    window.lireRecents = function () {
        try { return JSON.parse(localStorage.getItem(CLE_RECENTS) || '[]'); } catch (e) { return []; }
    };

    window.ajouterStickerRecent = function (sticker) {
        try {
            const liste = window.lireRecents().filter(s => s !== sticker);
            liste.unshift(sticker);
            localStorage.setItem(CLE_RECENTS, JSON.stringify(liste.slice(0, 16)));
        } catch (e) { /* stockage indisponible */ }
        window.majGrilleRecents();
    };

    window.majGrilleRecents = function () {
        const grille = document.getElementById('grilleRecents');
        if (!grille) return;
        const recents = window.lireRecents();
        grille.innerHTML = recents.length
            ? recents.map(s => '<span onclick="envoyerSticker(\'' + s + '\')">' + s + '</span>').join('')
            : '<p class="sticker-vide">Vos stickers récents apparaîtront ici.</p>';
    };

    /* Bascule d'un onglet de pack. */
    window.ouvrirPack = function (pack, bouton) {
        document.querySelectorAll('.sticker-tab').forEach(b => b.classList.toggle('active', b === bouton));
        document.querySelectorAll('#stickerGrids .sticker-grid').forEach(g => {
            g.style.display = g.getAttribute('data-pack') === pack ? 'grid' : 'none';
        });
    };

    window.majGrilleRecents();

    /* ---------- Message vocal ---------- */
    let mediaRecorder = null;
    let morceaux = [];
    let minuteurRec = null;

    window.toggleVoice = async function () {
        const bouton = document.getElementById('voiceBtn');
        const statut = document.getElementById('recStatus');
        const input = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');

        if (mediaRecorder && mediaRecorder.state === 'recording') { mediaRecorder.stop(); return; }

        if (!navigator.mediaDevices || !window.MediaRecorder) {
            alert("L'enregistrement audio n'est pas pris en charge par ce navigateur.");
            return;
        }

        let flux;
        try { flux = await navigator.mediaDevices.getUserMedia({ audio: true }); }
        catch (e) { alert('Micro inaccessible : ' + e.message); return; }

        morceaux = [];
        mediaRecorder = new MediaRecorder(flux);
        mediaRecorder.ondataavailable = e => { if (e.data.size) morceaux.push(e.data); };
        mediaRecorder.onstop = async () => {
            flux.getTracks().forEach(t => t.stop());
            clearInterval(minuteurRec);
            statut.classList.remove('on');
            input.style.display = '';
            sendBtn.style.display = '';
            bouton.classList.remove('icon-btn--rec');
            const type = mediaRecorder.mimeType || 'audio/webm';
            const extension = type.includes('mp4') ? 'm4a' : 'webm';
            await envoyerVocal(new File([new Blob(morceaux, { type })], 'vocal.' + extension, { type }));
        };

        mediaRecorder.start();
        bouton.classList.add('icon-btn--rec');
        statut.classList.add('on');
        input.style.display = 'none';
        sendBtn.style.display = 'none';

        let secondes = 0;
        document.getElementById('recTimer').textContent = '0:00';
        minuteurRec = setInterval(() => {
            secondes++;
            document.getElementById('recTimer').textContent = Math.floor(secondes / 60) + ':' + String(secondes % 60).padStart(2, '0');
        }, 1000);
    };

    async function envoyerVocal (fichierVocal) {
        const donnees = new FormData();
        donnees.append('_token', CSRF);
        donnees.append('piece_jointe', fichierVocal);

        try {
            // Aucun fil ouvert : on démarre la conversation avec le destinataire choisi.
            if (!REPONDRE_URL) {
                const sel = document.getElementById('nouveauDest');
                const dest = sel ? sel.value : null;
                if (!dest) { alert('Choisissez d’abord un destinataire.'); return; }
                donnees.append('sujet', 'Discussion');
                donnees.append('destinataires[]', dest);
                const rep = await fetch(STORE_URL, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: donnees,
                    credentials: 'same-origin',
                });
                const json = await rep.json();
                if (!rep.ok) throw json;
                window.location.assign(ESPACE_URL + '?conversation=' + json.conversation_id);
                return;
            }

            const fil = document.getElementById('messagesContainer');
            const rep = await fetch(REPONDRE_URL, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: donnees,
                credentials: 'same-origin',
            });
            const json = await rep.json();
            if (!rep.ok) throw json;
            if (fil) { fil.insertAdjacentHTML('beforeend', json.message.html); fil.scrollTop = fil.scrollHeight; }
        } catch (e) {
            alert('Envoi du vocal impossible.');
        }
    }

    /* ---------- Répondre à un message (citation) ---------- */
    window.repondreA = function (id) {
        const conteneur = document.querySelector('[data-message-id="' + id + '"]');
        const bloc = conteneur ? conteneur.querySelector('.bulle') : null;
        if (!bloc) return;
        reponseAId = id;
        document.getElementById('replyExtrait').textContent = bloc.textContent.trim().slice(0, 90);
        const auteur = document.getElementById('replyAuteur');
        if (auteur) auteur.textContent = conteneur.dataset.auteur || '';
        // On surligne la bulle visée pour voir clairement à quoi on répond.
        document.querySelectorAll('.bulle-cible').forEach(e => e.classList.remove('bulle-cible'));
        conteneur.classList.add('bulle-cible');
        const barre = document.getElementById('replyBar');
        barre.style.display = 'flex';
        const input = document.getElementById('messageInput');
        if (input) input.focus();
    };

    window.annulerReponse = function () {
        reponseAId = null;
        const barre = document.getElementById('replyBar');
        if (barre) barre.style.display = 'none';
        document.querySelectorAll('.bulle-cible').forEach(e => e.classList.remove('bulle-cible'));
    };

    /* Sauter à la bulle citée, puis la faire clignoter brièvement. */
    window.allerAuMessage = function (id) {
        const cible = document.querySelector('[data-message-id="' + id + '"]');
        if (!cible) return;
        cible.scrollIntoView({ behavior: 'smooth', block: 'center' });
        cible.classList.add('bulle-flash');
        setTimeout(() => cible.classList.remove('bulle-flash'), 1300);
    };

    /* ---------- Réactions émoji ---------- */
    window.toggleReactions = function (id, bouton) {
        const pop = document.getElementById('reactionPop');
        pop.dataset.messageId = id;
        pop.classList.add('on');
        const r = bouton.getBoundingClientRect();
        pop.style.left = Math.max(8, r.left - 40) + 'px';
        pop.style.top = (r.top - 46) + 'px';
    };

    document.getElementById('reactionPop')?.addEventListener('click', async function (e) {
        const cible = e.target.closest('span[data-emoji]');
        if (!cible) return;
        const id = this.dataset.messageId;
        const emoji = cible.dataset.emoji;
        this.classList.remove('on');
        try {
            const donnees = new FormData();
            donnees.append('_token', CSRF);
            donnees.append('reaction', emoji);
            const rep = await fetch(REAGIR_BASE.replace('__ID__', id), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: donnees,
                credentials: 'same-origin',
            });
            const json = await rep.json();
            if (!rep.ok) throw json;
            const el = document.querySelector('[data-message-id="' + id + '"]');
            if (el && json.message.html) el.outerHTML = json.message.html;
        } catch (err) {
            alert('Impossible de réagir.');
        }
    });

    document.addEventListener('click', function (e) {
        const pop = document.getElementById('reactionPop');
        if (pop && pop.classList.contains('on') && !pop.contains(e.target) && !e.target.closest('.bulle-action')) {
            pop.classList.remove('on');
        }
    });

    /* ---------- Correspondants / Discussions ---------- */
    window.msgTab = function (cible, bouton) {
        document.querySelectorAll('.msg-tab').forEach(b => b.classList.toggle('active', b === bouton));
        document.querySelectorAll('[data-vue]').forEach(el => {
            el.style.display = el.getAttribute('data-vue') === cible ? '' : 'none';
        });
    };

    /* Retour (vue mobile) : on revient sur la liste des CORRESPONDANTS, pour
       retrouver les autres personnes, sans recharger la page. */
    window.retourListe = function () {
        const shell = document.getElementById('msgShell');
        if (shell) {
            shell.classList.remove('show-thread');
            shell.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        const onglet = document.querySelector('.msg-tab[data-cible="correspondants"]');
        if (onglet) window.msgTab('correspondants', onglet);
    };

    /* Rouvrir la conversation chargée depuis la liste (vue mobile). */
    window.reprendreConversation = function () {
        const shell = document.getElementById('msgShell');
        if (shell) {
            shell.classList.add('show-thread');
            shell.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    /* Revenir au message du moment (bas du fil). */
    window.allerEnBas = function () {
        const fil = document.getElementById('messagesContainer');
        if (!fil) return;
        fil.scrollTo({ top: fil.scrollHeight, behavior: 'smooth' });
        const bouton = document.getElementById('btnBas');
        if (bouton) bouton.classList.remove('on');
    };

    /* Vue mobile : on ouvre d'abord sur les correspondants (les autres personnes). */
    if (window.matchMedia('(max-width: 820px)').matches) {
        const onglet = document.querySelector('.msg-tab[data-cible="correspondants"]');
        if (onglet) window.msgTab('correspondants', onglet);
    }

    window.ouvrirDiscussion = async function (id) {
        try {
            const donnees = new FormData();
            donnees.append('_token', CSRF);
            donnees.append('destinataire', id);
            const rep = await fetch(DEMARRER_URL, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: donnees,
                credentials: 'same-origin',
            });
            const json = await rep.json();
            if (!rep.ok) throw json;
            window.location.assign(ESPACE_URL + '?conversation=' + json.conversation_id);
        } catch (e) {
            alert('Impossible d’ouvrir cette conversation.');
        }
    };

    /* ---------- Retirer une discussion de SA liste ---------- */
    window.supprimerDiscussion = async function (url) {
        if (!confirm('Retirer cette discussion de votre liste ? L’autre participant conserve les messages.')) return;
        try {
            const rep = await fetch(url, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!rep.ok) throw new Error('http ' + rep.status);
            window.location.assign(ESPACE_URL);
        } catch (e) {
            alert('Suppression impossible.');
        }
    };

    // Délégation : l'icône poubelle ne déclenche pas l'ouverture de la discussion.
    document.getElementById('convContainer')?.addEventListener('click', function (evenement) {
        const bouton = evenement.target.closest('.conv__del');
        if (!bouton) return;
        evenement.preventDefault();
        evenement.stopPropagation();
        window.supprimerDiscussion(bouton.dataset.url);
    });

    /* ---------- Nouvelle conversation ---------- */
    window.ouvrirCompose = () => document.getElementById('composeModal').classList.add('on');
    window.fermerCompose = () => document.getElementById('composeModal').classList.remove('on');

    document.getElementById('composeSend')?.addEventListener('click', async function () {
        const select = document.getElementById('composeDest');
        const choix = Array.from(select.selectedOptions).map(o => o.value).filter(Boolean);
        const sujet = document.getElementById('composeSujet').value.trim();
        const corps = document.getElementById('composeCorps').value.trim();
        if (!choix.length || !sujet || !corps) { alert('Destinataire, objet et message sont obligatoires.'); return; }

        this.disabled = true;
        try {
            const donnees = new FormData();
            donnees.append('_token', CSRF);
            donnees.append('sujet', sujet);
            donnees.append('corps', corps);
            choix.forEach(c => donnees.append('destinataires[]', c));
            const rep = await fetch(STORE_URL, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: donnees,
                credentials: 'same-origin',
            });
            const json = await rep.json();
            if (!rep.ok) throw json;
            window.location.assign(ESPACE_URL + '?conversation=' + json.conversation_id);
        } catch (e) {
            alert('Envoi impossible. Vérifiez le destinataire.');
        } finally {
            this.disabled = false;
        }
    });

    /* ---------- Filtre ---------- */
    document.getElementById('convSearch')?.addEventListener('keyup', function () {
        const filtre = this.value.toLowerCase().trim();
        document.querySelectorAll('#convContainer .conv, #contactsContainer .conv').forEach(conv => {
            conv.style.display = (conv.getAttribute('data-search') || '').includes(filtre) ? 'flex' : 'none';
        });
    });
</script>
@endonce

@include('messagerie._appels', ['nomCorrespondant' => $active ? $nomConversation($active) : null])

@include('messagerie._profil', [
    'correspondantId' => $active ? $active->participants->firstWhere('id', '!=', $user->id)?->id : null,
])
