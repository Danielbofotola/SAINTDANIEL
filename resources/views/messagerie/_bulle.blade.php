@php($estMoi = $message->auteur_id === $user->id)
@php($reponse = $message->reponseA)
@php($appel = $message->appel_id ? $message->appel : null)

@if ($message->appel_id)
    {{-- Événement d'appel : sonnerie, manqué, refusé, ou terminé avec durée. --}}
    <div class="bulle-appel {{ $appel && ($appel->estManque() || $appel->statut === 'refuse') ? 'bulle-appel--rate' : '' }}"
         data-message-id="{{ $message->id }}">
        <span class="bulle-appel__rond"><i class="fa-solid {{ ($appel->type ?? 'audio') === 'video' ? 'fa-video' : 'fa-phone' }}"></i></span>
        <span class="bulle-appel__texte">
            <span class="bulle-appel__titre">
                {{ $appel?->libelleType() ?? 'Appel' }}
                <span class="bulle-appel__etat">{{ $appel?->libelleEtat() ?? 'indisponible' }}</span>
            </span>
            <span class="bulle-appel__heure">{{ $message->created_at?->format('H:i · d M') }} · {{ $estMoi ? 'sortant' : $message->nomAuteur() }}</span>
        </span>
        <button type="button" class="bulle-appel__rappel" title="Rappeler" onclick="appelDemarrer('{{ $appel->type ?? 'audio' }}')">
            <i class="fa-solid {{ ($appel->type ?? 'audio') === 'video' ? 'fa-video' : 'fa-phone' }}"></i> Rappeler
        </button>
    </div>
@else
<div class="bulle-container {{ $estMoi ? 'bulle-container--moi' : 'bulle-container--autre' }}"
     data-message-id="{{ $message->id }}"
     data-auteur="{{ $estMoi ? 'vous' : $message->nomAuteur() }}">
    @if ($message->auteur?->photo)
        <img class="msg-avatar msg-avatar--sm" src="{{ asset('storage/'.$message->auteur->photo) }}" alt="{{ $message->nomAuteur() }}">
    @else
        <span class="msg-avatar msg-avatar--sm">
            {{ mb_strtoupper(mb_substr($message->auteur?->prenom ?: $message->nomAuteur(), 0, 1)) }}
        </span>
    @endif

    <div class="bulle-colonne">
        @unless ($estMoi || ($active->type ?? 'directe') === 'directe')
            <strong class="bulle-auteur">{{ $message->nomAuteur() }}</strong>
        @endunless

        @if ($reponse)
            <div class="bulle-citation" onclick="allerAuMessage({{ $reponse->id }})" title="Voir le message d'origine">
                <span class="bulle-citation__auteur">
                    <i class="fa-solid fa-reply"></i>
                    {{ $reponse->auteur_id === $user->id ? 'Vous' : $reponse->nomAuteur() }}
                </span>
                <span class="bulle-citation__texte">{{ \Illuminate\Support\Str::limit($reponse->corps ?: $reponse->nomPieceJointe(), 60) }}</span>
            </div>
        @endif

        <div class="bulle {{ $estMoi ? 'bulle--moi' : 'bulle--autre' }} {{ $message->estSticker() ? 'bulle--sticker' : '' }}">
            {{ $message->corps }}
            @if ($message->piece_jointe)
                <div class="bulle__piece">
                    @if ($message->estAudio())
                        <audio class="bulle__audio" controls preload="metadata" src="{{ $message->urlPieceJointe() }}"></audio>
                    @elseif ($message->estImage())
                        <a href="{{ $message->urlPieceJointe() }}" target="_blank" rel="noopener">
                            <img src="{{ $message->urlPieceJointe() }}" alt="{{ $message->nomPieceJointe() }}" loading="lazy">
                        </a>
                    @else
                        <a href="{{ $message->urlPieceJointe() }}" target="_blank" rel="noopener">
                            <i class="fa fa-paperclip"></i> {{ $message->nomPieceJointe() }}
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if ($message->aReaction())
            <span class="bulle-reaction">{{ $message->reaction }}</span>
        @endif

        <div class="bulle-pied">
            <span class="bulle__meta">{{ $message->created_at?->format('H:i | d M') }}</span>
            <button type="button" class="bulle-action" title="Répondre" onclick="repondreA({{ $message->id }})">
                <i class="fa-solid fa-reply"></i>
            </button>
            <button type="button" class="bulle-action" title="Réagir" onclick="toggleReactions({{ $message->id }}, this)">
                <i class="fa-regular fa-face-smile"></i>
            </button>
        </div>
    </div>
</div>
@endif

@once
<style>
    .bulle__piece { margin-top: 6px; }
    .bulle__piece img { max-width: 240px; max-height: 240px; border-radius: 8px; display: block; cursor: zoom-in; }
    .bulle__piece a { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: inherit; text-decoration: underline; }
    .bulle__audio { display: block; max-width: 260px; height: 38px; }
    .bulle--sticker { background: transparent !important; font-size: 3rem; line-height: 1; padding: 0 4px; margin: 2px 0; }
</style>
@endonce
