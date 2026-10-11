<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mes notifications | {{ $systemSettings?->nom_court ?? 'Établissement' }}</title>

    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/font-awesome-6.5.1/css/all.min.css') }}">

    <style>
        :root { --bg: #0b0b0c; --card: #242526; --divider: #2f3031; --text: #e4e6eb; --muted: #b0b3b8; --blue: #0866ff; --header: #6a1b9a; }
        body { background: var(--bg); color: var(--text); margin: 0; overflow-x: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        a { text-decoration: none; }
        .notif-header { display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 20px; background: var(--card); border-bottom: 1px solid var(--divider); position: sticky; top: 0; z-index: 900; }
        .notif-header h1 { margin: 0; font-size: 1.1rem; color: #fff; }
        .notif-header .sous-titre { color: var(--muted); font-size: .78rem; }
        .notif-header .actions { display: flex; align-items: center; gap: 8px; }
        .btn-top { display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px; border-radius: 999px;
            font-size: .82rem; font-weight: 600; color: #fff; background: var(--blue); border: none; cursor: pointer; }
        .btn-top--neutral { background: #3a3b3c; color: var(--text); }
        .btn-top[disabled] { opacity: .5; cursor: default; }
        .notif-contenu { max-width: 900px; margin: 0 auto; padding: 16px; }
        .notif-carte { background: var(--card); border: 1px solid var(--divider); border-radius: 12px; padding: 14px 16px; margin-bottom: 12px; }
        .notif-carte--nouvelle { border-left: 4px solid var(--blue); }
        .badge-niveau { font-size: .68rem; font-weight: 700; padding: 2px 8px; border-radius: 999px; }
        .badge-info { background: #1e3a8a; color: #dbeafe; }
        .badge-success { background: #14532d; color: #dcfce7; }
        .badge-warning { background: #78350f; color: #fef3c7; }
        .badge-danger { background: #7f1d1d; color: #fee2e2; }
        .badge-nouveau { background: var(--blue); color: #fff; font-size: .62rem; font-weight: 700; padding: 2px 8px; border-radius: 999px; margin-left: 6px; }
        .notif-titre { color: #fff; font-weight: 700; }
        .notif-message { color: var(--text); font-size: .9rem; margin-top: 4px; }
        .notif-meta { color: var(--muted); font-size: .76rem; margin-top: 6px; }
        .notif-actions { display: flex; gap: 8px; align-items: flex-start; }
        .btn-lire { background: transparent; border: 1px solid var(--divider); color: var(--muted); border-radius: 8px;
            padding: 6px 12px; font-size: .78rem; cursor: pointer; white-space: nowrap; }
        .btn-lire:hover { color: #fff; }
        .alerte { border-radius: 8px; padding: 10px 14px; font-size: .85rem; margin-bottom: 12px; }
        .alerte--ok { background: #14532d; color: #dcfce7; }
        .alerte--ko { background: #7f1d1d; color: #fee2e2; }
        .vide { text-align: center; color: var(--muted); padding: 40px 16px; }
        .notif-pagination { margin-top: 16px; }
        .notif-pagination a, .notif-pagination span { color: var(--muted); }
        @media (max-width: 820px) { .btn-top span { display: none; } .notif-header h1 { font-size: .95rem; } }
    </style>
</head>
<body>

    <header class="notif-header">
        <div>
            <h1><i class="fa-solid fa-bell"></i> Mes notifications</h1>
            <div class="sous-titre">{{ $notifications->total() }} notification(s) · {{ $nonLues }} non lue(s)</div>
        </div>
        <div class="actions">
            <form method="POST" action="{{ route('notifications.readall') }}">
                @csrf
                @method('PATCH')
                <button class="btn-top btn-top--neutral" type="submit" @disabled($nonLues === 0)>
                    <i class="fa-solid fa-check-double"></i><span>Tout marquer comme lu</span>
                </button>
            </form>
            <a class="btn-top" href="{{ route($user->dashboardRouteName()) }}"><i class="fa-solid fa-gauge"></i><span>Tableau de bord</span></a>
        </div>
    </header>

    <main class="notif-contenu">
        @if (session('success'))<div class="alerte alerte--ok">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alerte alerte--ko">{{ session('error') }}</div>@endif

        @forelse ($notifications as $notification)
            @php($donnees = $notification->data)
            @php($niveau = $donnees['niveau'] ?? 'info')
            <div class="notif-carte {{ $notification->read_at ? '' : 'notif-carte--nouvelle' }}">
                <div class="d-flex justify-content-between align-items-start" style="gap:12px">
                    <div style="min-width:0">
                        <span class="badge-niveau badge-{{ in_array($niveau, ['info','success','warning','danger'], true) ? $niveau : 'info' }}">
                            {{ \App\Models\Annonce::NIVEAUX[$niveau] ?? 'Information' }}
                        </span>
                        @unless ($notification->read_at)
                            <span class="badge-nouveau">Nouveau</span>
                        @endunless
                        <span class="notif-titre">{{ $donnees['titre'] ?? 'Notification' }}</span>
                        <div class="notif-message">{{ $donnees['message'] ?? '' }}</div>
                        <div class="notif-meta">
                            <i class="fa-regular fa-user"></i> {{ $donnees['auteur'] ?? 'administration' }}
                            · <i class="fa-regular fa-clock"></i> {{ $notification->created_at?->format('d/m/Y H:i') }}
                            @if (! empty($donnees['lien']))
                                · <i class="fa-solid fa-link"></i> {{ $donnees['lien'] }}
                            @endif
                        </div>
                    </div>
                    <div class="notif-actions">
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn-lire" type="submit">
                                <i class="fa {{ $notification->read_at ? 'fa-eye' : 'fa-check' }}"></i>
                                {{ $notification->read_at ? 'Ouvrir' : 'Marquer comme lue' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="vide">
                <i class="fa-regular fa-bell-slash fa-3x mb-3"></i>
                <p>Aucune notification pour l'instant.</p>
            </div>
        @endforelse

        <div class="notif-pagination">{{ $notifications->links() }}</div>
    </main>
</body>
</html>
