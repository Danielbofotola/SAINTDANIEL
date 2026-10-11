@php
    $nomUtilisateur = trim(($user->nom ?? '').' '.($user->postnom ?? '').' '.($user->prenom ?? '')) ?: $user->username;
    $initiales = mb_strtoupper(mb_substr($user->prenom ?: $user->username, 0, 1));
    $nbNonLues = $user->notifications()->whereNull('read_at')->count();
    $nbNonLus = \App\Support\Messagerie::nonLus($user);
@endphp
<!DOCTYPE html>
<html lang="fr" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titre', 'Espace Parent') | {{ $parametres->nom_court }}</title>

    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/font-awesome-6.5.1/css/all.min.css') }}">

    <style>
        :root {
            --bg: #0b0b0c;
            --card: #242526;
            --header: #6a1b9a;
            --text: #e4e6eb;
            --muted: #b0b3b8;
            --blue: #0866ff;
            --divider: #2f3031;
        }
        body { background: var(--bg); color: var(--text); margin: 0; overflow-x: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        a { color: var(--blue); text-decoration: none; }
        a:hover { text-decoration: none; }

        .portail-header { background: var(--header); color: #fff; display: flex; align-items: center;
            justify-content: space-between; gap: 12px; padding: 8px 16px; position: sticky; top: 0; z-index: 1020; }
        .portail-header__brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: .92rem; }
        .portail-header__user { display: flex; align-items: center; gap: 10px; font-size: .8rem; }
        .portail-avatar { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,.25); color: #fff;
            display: inline-flex; align-items: center; justify-content: center; font-weight: 700; overflow: hidden; flex: none; }
        .portail-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .btn-ghost { background: rgba(255,255,255,.16); color: #fff; border: 1px solid rgba(255,255,255,.35);
            border-radius: 999px; padding: 4px 12px; font-size: .78rem; cursor: pointer; }
        .btn-ghost:hover { background: rgba(255,255,255,.28); color: #fff; }

        .portail-nav { display: flex; justify-content: space-around; align-items: center; background: var(--card);
            border-bottom: 1px solid var(--divider); position: sticky; top: 50px; z-index: 1010; }
        .portail-nav__item { color: var(--muted); cursor: pointer; padding: 10px 18px; border-bottom: 3px solid transparent;
            font-size: 1.3rem; display: flex; align-items: center; gap: 8px; position: relative; }
        .portail-nav__item span { font-size: .82rem; font-weight: 600; }
        .portail-nav__item.active { color: var(--blue); border-bottom-color: var(--blue); }
        .portail-nav__badge { position: absolute; top: 2px; right: 6px; background: #e41e3f; color: #fff;
            font-size: .62rem; font-weight: 700; padding: 1px 5px; border-radius: 10px; }

        .portail-layout { max-width: 1180px; margin: 0 auto; padding: 16px; }
        .portail-card { background: var(--card); border: 1px solid var(--divider); border-radius: 12px; padding: 16px; margin-bottom: 14px; }
        .portail-card h2 { font-size: 1.05rem; margin: 0 0 12px; color: #fff; }
        .fs-7 { font-size: .8rem; }
        .text-muted-2 { color: var(--muted); }
        .badge-niveau { font-size: .68rem; font-weight: 700; padding: 2px 8px; border-radius: 999px; }
        .badge-info { background: #1e3a8a; color: #dbeafe; }
        .badge-success { background: #14532d; color: #dcfce7; }
        .badge-warning { background: #78350f; color: #fef3c7; }
        .badge-danger { background: #7f1d1d; color: #fee2e2; }
        .info-row { display: flex; align-items: center; gap: 12px; padding: 7px 0; font-size: .92rem; }
        .info-row i { width: 22px; text-align: center; color: var(--muted); }

        @media (max-width: 820px) {
            .portail-nav__item span { display: none; }
            .portail-nav__item { font-size: 1.45rem; padding: 12px 22px; }
        }
    </style>
</head>
<body>

    <header class="portail-header">
        <div class="portail-header__brand">
            <i class="fa-solid fa-graduation-cap fs-5"></i>
            <span>Espace Parent — {{ $parametres->nom_court }}</span>
        </div>
        <div class="portail-header__user">
            <span class="d-none d-sm-inline">{{ $nomUtilisateur }}</span>
            <span class="portail-avatar">
                @if ($user->photo)
                    <img src="{{ asset('storage/'.$user->photo) }}" alt="{{ $nomUtilisateur }}">
                @else
                    {{ $initiales }}
                @endif
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-ghost" type="submit" title="Se déconnecter"><i class="fa-solid fa-power-off"></i></button>
            </form>
        </div>
    </header>

    {{-- Une page par rubrique : chaque lien recharge uniquement ses propres données. --}}
    <nav class="portail-nav">
        <a class="portail-nav__item {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}"
           href="{{ route('parent.dashboard') }}" title="Actualités">
            <i class="fa-solid fa-newspaper"></i><span>Actualités</span>
        </a>
        <a class="portail-nav__item {{ request()->routeIs('parent.notifications') ? 'active' : '' }}"
           href="{{ route('parent.notifications') }}" title="Notifications">
            <i class="fa-solid fa-bell"></i><span>Notifications</span>
            @if ($nbNonLues > 0)
                <span class="portail-nav__badge">{{ $nbNonLues }}</span>
            @endif
        </a>
        <a class="portail-nav__item {{ request()->routeIs('parent.messages') ? 'active' : '' }}"
           href="{{ route('parent.messages') }}" title="Messages">
            <i class="fa-brands fa-facebook-messenger"></i><span>Messages</span>
            @if ($nbNonLus > 0)
                <span class="portail-nav__badge" data-non-lus>{{ $nbNonLus }}</span>
            @endif
        </a>
        <a class="portail-nav__item {{ request()->routeIs('parent.enfants') ? 'active' : '' }}"
           href="{{ route('parent.enfants') }}" title="Mes enfants">
            <i class="fa-solid fa-graduation-cap"></i><span>Mes enfants</span>
        </a>
        <a class="portail-nav__item {{ request()->routeIs('parent.profil') ? 'active' : '' }}"
           href="{{ route('parent.profil') }}" title="Profil">
            <i class="fa-solid fa-circle-user"></i><span>Profil</span>
        </a>
    </nav>

    <main class="portail-layout" id="contenu">
        @yield('contenu')
    </main>

    @stack('scripts')
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
