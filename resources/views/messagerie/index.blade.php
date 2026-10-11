<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Messagerie | {{ $systemSettings?->nom_court ?? 'Établissement' }}</title>
    <link rel="canonical" href="{{ route('messagerie.index') }}">

    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/font-awesome-6.5.1/css/all.min.css') }}">

    <style>
        :root { --bg: #0b0b0c; --card: #242526; --divider: #2f3031; --text: #e4e6eb; --muted: #b0b3b8; --blue: #0866ff; }
        body { background: var(--bg); color: var(--text); margin: 0; overflow-x: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        a { text-decoration: none; }
        .messagerie-header { display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 20px; background: var(--card); border-bottom: 1px solid var(--divider); position: sticky; top: 0; z-index: 900; }
        .messagerie-header h1 { margin: 0; font-size: 1.1rem; color: #fff; }
        .messagerie-header .sous-titre { color: var(--muted); font-size: .78rem; }
        .messagerie-header .actions { display: flex; align-items: center; gap: 8px; }
        .btn-top { display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px; border-radius: 999px;
            font-size: .82rem; font-weight: 600; color: #fff; background: var(--blue); }
        .btn-top--neutral { background: #3a3b3c; color: var(--text); }
        .btn-top:hover { color: #fff; }
        .messagerie-contenu { max-width: 1180px; margin: 0 auto; padding: 16px; }
        .alerte { border-radius: 8px; padding: 10px 14px; font-size: .85rem; margin-bottom: 12px; }
        .alerte--ok { background: #14532d; color: #dcfce7; }
        .alerte--ko { background: #7f1d1d; color: #fee2e2; }
        @media (max-width: 820px) { .messagerie-header h1 { font-size: .95rem; } .btn-top span { display: none; } }
    </style>
</head>
<body>

    <header class="messagerie-header">
        <div>
            <h1><i class="fa-solid fa-comments"></i> Messagerie interne</h1>
            <div class="sous-titre">{{ $conversations->count() }} conversation(s) · {{ $nonLus }} non lu(s)</div>
        </div>
        <div class="actions">
            <a class="btn-top btn-top--neutral" href="{{ route('notifications.index') }}"><i class="fa-solid fa-bell"></i><span>Notifications</span></a>
            <a class="btn-top" href="{{ route($user->dashboardRouteName()) }}"><i class="fa-solid fa-gauge"></i><span>Tableau de bord</span></a>
        </div>
    </header>

    <main class="messagerie-contenu">
        @if (session('success'))<div class="alerte alerte--ok">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alerte alerte--ko">{{ session('error') }}</div>@endif

        @include('messagerie._espace', [
            'user' => $user,
            'conversations' => $conversations,
            'active' => $active,
            'messages' => $messages,
            'destinataires' => $destinataires,
            'nonLus' => $nonLus,
            'espaceUrl' => route('messagerie.index'),
            'avecFichier' => true,
            'composeMultiple' => true,
        ])
    </main>
</body>
</html>
