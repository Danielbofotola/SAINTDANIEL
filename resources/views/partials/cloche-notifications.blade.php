@php($utilisateur = auth()->user())
@if ($utilisateur && \Illuminate\Support\Facades\Schema::hasTable('notifications'))
    @php($nonLues = $utilisateur->unreadNotifications()->count())
    @php($dernieres = $utilisateur->notifications()->latest()->limit(5)->get())
    <li class="dropdown">
        <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Notifications" style="position:relative">
            <i class="fa fa-bell-o fa-lg"></i>
            @if ($nonLues > 0)
                <span class="badge badge-danger" style="position:absolute;top:4px;right:0">{{ $nonLues }}</span>
            @endif
        </a>
        <ul class="dropdown-menu settings-menu dropdown-menu-right border-0 shadow-lg">
            <li class="dropdown-header">Notifications @if ($nonLues > 0)<span class="badge badge-danger ml-1">{{ $nonLues }}</span>@endif</li>
            @forelse ($dernieres as $notification)
                @php($donnees = $notification->data)
                <li>
                    <a class="dropdown-item" href="{{ route('notifications.index') }}">
                        <strong>{{ $donnees['titre'] ?? 'Notification' }}</strong>
                        <small class="d-block text-muted">{{ \Illuminate\Support\Str::limit($donnees['message'] ?? '', 60) }}</small>
                        <small class="d-block text-muted">{{ $notification->created_at?->diffForHumans() }}</small>
                    </a>
                </li>
            @empty
                <li><span class="dropdown-item-text text-muted">Aucune notification</span></li>
            @endforelse
            <li><a class="dropdown-item text-primary" href="{{ route('notifications.index') }}"><i class="fa fa-list"></i> Voir toutes mes notifications</a></li>
        </ul>
    </li>
@endif
