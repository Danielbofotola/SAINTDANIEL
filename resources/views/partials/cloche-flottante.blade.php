@php($utilisateur = auth()->user())
@if ($utilisateur && \Illuminate\Support\Facades\Schema::hasTable('notifications'))
    @php($nonLues = $utilisateur->unreadNotifications()->count())
    @php($dernieres = $utilisateur->notifications()->latest()->limit(5)->get())
    <div class="cloche-flottante no-print">
        @php($messagesNonLus = \Illuminate\Support\Facades\Schema::hasTable('conversations') ? \App\Support\Messagerie::nonLus($utilisateur) : 0)
        <a class="cloche-flottante__lien" href="{{ route('messagerie.index') }}" aria-label="Messagerie">
            <i class="fa fa-comments"></i>
            @if ($messagesNonLus > 0)<span class="cloche-flottante__badge" data-non-lus>{{ $messagesNonLus > 99 ? '99+' : $messagesNonLus }}</span>@endif
        </a>
        <button type="button" class="cloche-flottante__btn" aria-label="Notifications" aria-expanded="false">
            <i class="fa fa-bell"></i>
            @if ($nonLues > 0)<span class="cloche-flottante__badge" data-non-lues>{{ $nonLues > 99 ? '99+' : $nonLues }}</span>@endif
        </button>
        <div class="cloche-flottante__panneau" hidden>
            <div class="cloche-flottante__titre">
                Notifications
                @if ($nonLues > 0)<span class="cloche-flottante__badge" data-non-lues>{{ $nonLues }}</span>@endif
            </div>
            @forelse ($dernieres as $notification)
                @php($donnees = $notification->data)
                <a class="cloche-flottante__item" href="{{ route('notifications.index') }}">
                    <strong>{{ $donnees['titre'] ?? 'Notification' }}</strong>
                    <small>{{ \Illuminate\Support\Str::limit($donnees['message'] ?? '', 70) }}</small>
                    <small class="cloche-flottante__meta">{{ $notification->created_at?->diffForHumans() }}</small>
                </a>
            @empty
                <div class="cloche-flottante__vide">Aucune notification</div>
            @endforelse
            <a class="cloche-flottante__pied" href="{{ route('notifications.index') }}">
                <i class="fa fa-list"></i> Voir toutes mes notifications
            </a>
        </div>
    </div>

    <style>
        .cloche-flottante { position: fixed; top: 14px; right: 14px; z-index: 1080; font-family: 'Public Sans', system-ui, sans-serif; }
        .cloche-flottante__btn { width: 44px; height: 44px; border-radius: 50%; border: 0; cursor: pointer; background: #0f172a; color: #fff;
            box-shadow: 0 6px 18px rgba(15,23,42,.28); font-size: 17px; position: relative; }
        .cloche-flottante__btn:hover { background: #1e293b; }
        .cloche-flottante__lien { display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 50%;
            background: #107c41; color: #fff; font-size: 17px; text-decoration: none; position: relative; margin-right: 8px;
            box-shadow: 0 6px 18px rgba(15,23,42,.28); }
        .cloche-flottante__lien:hover { background: #0d6335; color: #fff; text-decoration: none; }
        .cloche-flottante__badge { position: absolute; top: -4px; right: -4px; background: #dc2626; color: #fff; border-radius: 999px;
            font-size: 11px; font-weight: 700; padding: 1px 6px; line-height: 16px; }
        .cloche-flottante__panneau { position: absolute; top: 52px; right: 0; width: min(340px, 92vw); background: #fff; border: 1px solid #e2e8f0;
            border-radius: 12px; box-shadow: 0 12px 32px rgba(15,23,42,.18); overflow: hidden; }
        .cloche-flottante__titre { font-weight: 700; font-size: 13px; color: #1e293b; padding: 10px 14px; border-bottom: 1px solid #e2e8f0; position: relative; }
        .cloche-flottante__item { display: block; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; text-decoration: none; color: #1e293b; }
        .cloche-flottante__item:hover { background: #f8fafc; text-decoration: none; }
        .cloche-flottante__item strong { display: block; font-size: 13px; }
        .cloche-flottante__item small { display: block; color: #64748b; font-size: 12px; }
        .cloche-flottante__meta { color: #94a3b8 !important; }
        .cloche-flottante__vide { padding: 16px 14px; color: #64748b; font-size: 13px; }
        .cloche-flottante__pied { display: block; padding: 10px 14px; background: #f8fafc; color: #2563eb; font-size: 13px; font-weight: 600; text-decoration: none; }
        .cloche-flottante__pied:hover { text-decoration: none; background: #eff6ff; }
        @media print { .cloche-flottante { display: none !important; } }
    </style>

    <script>
        (function () {
            var racine = document.currentScript.previousElementSibling;
            while (racine && !racine.classList.contains('cloche-flottante')) { racine = racine.previousElementSibling; }
            if (!racine) { return; }

            var bouton = racine.querySelector('.cloche-flottante__btn');
            var panneau = racine.querySelector('.cloche-flottante__panneau');

            function basculer(ouvrir) {
                panneau.hidden = !ouvrir;
                bouton.setAttribute('aria-expanded', ouvrir ? 'true' : 'false');
            }

            bouton.addEventListener('click', function (evenement) {
                evenement.stopPropagation();
                basculer(panneau.hidden);
            });

            document.addEventListener('click', function (evenement) {
                if (!racine.contains(evenement.target)) { basculer(false); }
            });

            document.addEventListener('keydown', function (evenement) {
                if (evenement.key === 'Escape') { basculer(false); }
            });
        })();
    </script>
@endif
