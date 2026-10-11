@extends('parent.layout')

@section('titre', 'Profil')

@section('contenu')
    @php($nomUtilisateur = trim(($user->nom ?? '').' '.($user->postnom ?? '').' '.($user->prenom ?? '')) ?: $user->username)
    @php($initiales = mb_strtoupper(mb_substr($user->prenom ?: $user->username, 0, 1)))

    <div class="portail-card text-center">
        <span class="portail-avatar mx-auto mb-2" style="width:96px;height:96px;font-size:2rem;background:var(--blue)">
            @if ($user->photo)
                <img src="{{ asset('storage/'.$user->photo) }}" alt="{{ $nomUtilisateur }}">
            @else
                {{ $initiales }}
            @endif
        </span>
        <h2 class="mb-1">{{ $nomUtilisateur }}</h2>
        <div class="fs-7 text-muted-2">Espace Parent · {{ \App\Models\User::libelleRole($user->role) }}</div>
    </div>

    <div class="portail-card">
        <h2>Informations</h2>
        <div class="info-row"><i class="fa-solid fa-user"></i> <span>{{ $user->username }}</span></div>
        <div class="info-row"><i class="fa-solid fa-envelope"></i> <span>{{ $user->email }}</span></div>
        <div class="info-row"><i class="fa-solid fa-phone"></i> <span>{{ $user->telephone ?: '—' }}</span></div>
        <div class="info-row"><i class="fa-solid fa-location-dot"></i> <span>{{ $user->adresse ?: '—' }}</span></div>
        <div class="info-row"><i class="fa-solid fa-building"></i> <span>{{ $parametres->nom_etablissement }}</span></div>
        <div class="fs-7 text-muted-2 mt-2">
            <i class="fa-solid fa-lock"></i> Vos informations et coordonnées sont gérées par l’administration.
        </div>
        <div class="mt-3 d-flex flex-wrap gap-2">
            <a class="btn-ghost" href="{{ route('messagerie.index') }}"><i class="fa-solid fa-comments"></i> Messagerie interne</a>
            <a class="btn-ghost" href="{{ route('notifications.index') }}"><i class="fa-solid fa-bell"></i> Notifications</a>
        </div>
    </div>
@endsection
