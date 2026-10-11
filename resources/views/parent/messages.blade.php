@extends('parent.layout')

@section('titre', 'Messages')

@section('contenu')
    @if ($destinataires->isNotEmpty())
        @php($interlocuteur = $destinataires->first())
        @php($nomInterlocuteur = trim(($interlocuteur->nom ?? '').' '.($interlocuteur->prenom ?? '')) ?: $interlocuteur->username)
        {{-- Accès permanent en un clic : le parent n'a qu'un interlocuteur (le préfet). --}}
        <div class="portail-card" style="text-align:center">
            <span class="portail-avatar mx-auto mb-2" style="width:72px;height:72px;font-size:1.5rem;background:var(--blue)">
                @if ($interlocuteur->photo)
                    <img src="{{ asset('storage/'.$interlocuteur->photo) }}" alt="{{ $nomInterlocuteur }}">
                @else
                    {{ mb_strtoupper(mb_substr($interlocuteur->prenom ?: $interlocuteur->username, 0, 1)) }}
                @endif
            </span>
            <h2 class="mb-1">Écrire à {{ $nomInterlocuteur }}</h2>
            <div class="fs-7 text-muted-2 mb-3">{{ \App\Models\User::libelleRole($interlocuteur->role) }} — votre interlocuteur pour toute question.</div>
            <button type="button" class="btn-ghost" onclick="ouvrirDiscussion({{ $interlocuteur->id }})">
                <i class="fa-solid fa-comments"></i> {{ $conversations->isEmpty() ? 'Démarrer la discussion' : 'Ouvrir la discussion' }}
            </button>
        </div>
    @endif

    @include('messagerie._espace', [
        'user' => $user,
        'conversations' => $conversations,
        'active' => $active,
        'messages' => $messages,
        'destinataires' => $destinataires,
        'nonLus' => $nonLus,
        'espaceUrl' => route('parent.messages'),
        'avecFichier' => false,
        'composeMultiple' => false,
    ])
@endsection
