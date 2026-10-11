@extends('parent.layout')

@section('titre', 'Détail de la notification')

@section('contenu')
    @php($donnees = $notification->data)
    @php($libelles = ['info' => 'Information', 'success' => 'Réussite', 'warning' => 'À suivre', 'danger' => 'Important'])
    @php($niveau = $donnees['niveau'] ?? 'info')

    <div class="portail-card">
        <span class="badge-niveau badge-{{ $niveau }}">{{ $libelles[$niveau] ?? 'Information' }}</span>
        <h2 class="mt-2 mb-2">{{ $donnees['titre'] ?? 'Notification' }}</h2>

        <div style="white-space:pre-line;font-size:.96rem;color:var(--text)">{{ $donnees['message'] ?? '' }}</div>

        <div class="fs-7 text-muted-2 mt-3">
            De <strong>{{ $donnees['auteur'] ?? 'administration' }}</strong>
            · reçue le {{ $notification->created_at?->format('d/m/Y à H:i') }}
            @if ($notification->read_at)
                · <i class="fa-regular fa-envelope-open"></i> lue
            @endif
        </div>

        @if (! empty($donnees['lien']))
            <div class="mt-3">
                <a class="btn-ghost" href="{{ $donnees['lien'] }}">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    {{ isset($donnees['decision']) ? "Voir le dossier de l'enfant" : 'Ouvrir la page concernée' }}
                </a>
            </div>
        @endif
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a class="btn-ghost" href="{{ route('parent.notifications') }}">
            <i class="fa-solid fa-arrow-left"></i> Retour aux notifications
        </a>
    </div>
@endsection
