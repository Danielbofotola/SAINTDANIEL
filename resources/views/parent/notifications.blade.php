@extends('parent.layout')

@section('titre', 'Notifications')

@section('contenu')
    <div class="portail-card">
        <h2><i class="fa-solid fa-bell text-info"></i> Mes notifications</h2>
        @php($libelles = ['info' => 'Information', 'success' => 'Réussite', 'warning' => 'À suivre', 'danger' => 'Important'])
        @forelse ($notifications as $notification)
            @php($donnees = $notification->data)
            @php($niveau = $donnees['niveau'] ?? 'info')
            {{-- Chaque ligne ouvre le détail de la notification. --}}
            <a href="{{ route('parent.notification', $notification->id) }}" style="text-decoration:none;color:inherit">
                <div class="py-2 d-flex align-items-start gap-2" style="border-bottom:1px solid var(--divider); {{ $notification->read_at ? '' : 'font-weight:600;' }}">
                    <div style="flex:1;min-width:0">
                        <span class="badge-niveau badge-{{ $niveau }}">{{ $libelles[$niveau] ?? 'Information' }}</span>
                        <strong class="ms-1" style="color:#fff">{{ $donnees['titre'] ?? 'Notification' }}</strong>
                        <div class="fs-7 text-muted-2 mt-1">{{ \Illuminate\Support\Str::limit($donnees['message'] ?? '', 120) }}</div>
                        <div class="fs-7 text-muted-2 mt-1">
                            De {{ $donnees['auteur'] ?? 'administration' }} · {{ $notification->created_at?->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <span class="fs-7" style="color:var(--blue);white-space:nowrap">Détail ›</span>
                </div>
            </a>
        @empty
            <p class="text-muted-2 mb-0 fs-7">Aucune notification.</p>
        @endforelse
    </div>

    <div class="text-center mb-3">
        <a class="btn-ghost" href="{{ route('notifications.index') }}">Ouvrir la boîte de réception complète</a>
    </div>
@endsection
