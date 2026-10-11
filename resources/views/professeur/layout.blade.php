@php
    $user = $user ?? auth()->user();
    $professor = $professor ?? $user?->professeur;
    $displayName = $professor ? trim($professor->prenom.' '.$professor->nom) : ($user->prenom ?: $user->username);
    $initiale = mb_strtoupper(mb_substr($displayName, 0, 1));
    $photo = $user->photo ?: $professor?->photo;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Espace enseignant · @yield('titre', 'Vue générale') | {{ $systemSettings->nom_court }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">

    <style>
        :root {
            --able-primary: #4680ff;
            --able-primary-dark: #2f63d4;
            --able-bg: #f8fafc;
            --able-card-bg: #ffffff;
            --able-text-main: #1e293b;
            --able-text-muted: #64748b;
            --able-border: #e2e8f0;
        }

        body { font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: var(--able-bg); color: var(--able-text-main); }

        .app-header { background: #0f172a !important; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-bottom: 1px solid rgba(255,255,255,0.05); }
        .app-header__logo { background-color: #020617 !important; font-weight: 700; letter-spacing: 0.5px; font-size: 18px; }
        .app-header__user-avatar { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.2); }
        .app-header__user-initial { width: 35px; height: 35px; border-radius: 50%; background-color: var(--able-primary); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }

        .app-sidebar { background-color: #fff; border-right: 1px solid var(--able-border); box-shadow: none; }
        .app-sidebar__user { padding: 20px; background: #f8fafc; border-bottom: 1px solid var(--able-border); margin-bottom: 15px; display: flex; align-items: center; }
        .app-sidebar__user-avatar, .app-sidebar__user-initial { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }
        .app-sidebar__user-initial { background-color: var(--able-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; }
        .app-sidebar__user-name { color: var(--able-text-main); font-weight: 600; }
        .app-sidebar__user-designation { color: var(--able-text-muted); font-size: 12px; }

        .app-menu__item { border-radius: 8px; margin: 2px 12px; color: #475569; font-weight: 500; transition: all 0.2s ease; }
        .app-menu__item:hover, .app-menu__item.active { background: #eff6ff !important; color: var(--able-primary) !important; box-shadow: inset 4px 0 0 var(--able-primary); }
        .app-menu__item.active .app-menu__icon, .app-menu__item:hover .app-menu__icon { color: var(--able-primary) !important; }

        .stat-card { background: var(--able-card-bg); border-radius: 12px; padding: 20px; border: 1px solid var(--able-border); box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 16px; }
        .stat-icon { width: 52px; height: 52px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
        .stat-icon.primary { background: #eff6ff; color: #2563eb; }
        .stat-icon.info { background: #f0fdf4; color: #16a34a; }
        .stat-icon.warning { background: #fffbeb; color: #d97706; }
        .stat-icon.danger { background: #fef2f2; color: #dc2626; }
        .stat-icon.dark { background: #f1f5f9; color: #0f172a; }
        .stat-details h4 { font-size: 13px; font-weight: 600; color: var(--able-text-muted); margin-bottom: 4px; }
        .stat-details p { font-size: 22px; font-weight: 700; color: var(--able-text-main); margin: 0; }
        .stat-details small { display: block; margin-top: 2px; font-size: 12px; color: var(--able-text-muted); }

        .tile { background: var(--able-card-bg); border-radius: 12px; border: 1px solid var(--able-border); box-shadow: 0 1px 3px rgba(0,0,0,0.02); padding: 24px; margin-bottom: 24px; }
        .tile-title { font-size: 16px; font-weight: 700; color: var(--able-text-main); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; }

        .table { color: var(--able-text-main); }
        .table thead th { background-color: #f8fafc; border-bottom: 2px solid var(--able-border); color: var(--able-text-muted); font-size: 13px; font-weight: 600; }
        .table-hover tbody tr:hover { background-color: #f1f5f9; }

        .menu-section { padding: 16px 24px 6px; font-size: 12px; font-weight: 600; color: var(--able-text-muted); list-style: none; }
        .list-group-item { border-color: var(--able-border); padding: 14px 4px; }

        .row-item { display:flex; align-items:center; padding:12px 0; border-bottom:1px solid var(--able-border); }
        .row-item:last-child { border-bottom:0; }
        .row-time { width:64px; font-weight:600; color:var(--able-text-main); }
        .row-time small { display:block; font-weight:400; color:var(--able-text-muted); }
        .row-main { flex:1; min-width:0; }
        .row-title { font-weight:600; color:var(--able-text-main); }
        .row-meta { font-size:13px; color:var(--able-text-muted); }
        .date-chip { width:48px; text-align:center; background:#eef4ff; color:var(--able-primary); border-radius:8px; padding:6px 0; margin-right:14px; line-height:1.1; }
        .date-chip strong { display:block; font-size:18px; }
        .date-chip span { font-size:12px; }
        .pill { background:#f1f5f9; border-radius:999px; padding:2px 10px; font-size:12px; white-space:nowrap; }
        .empty { text-align:center; padding:28px 12px; color:var(--able-text-muted); }
        .empty i { font-size:26px; display:block; margin-bottom:8px; }
        .empty strong { display:block; color:var(--able-text-main); }

        .jour-bloc { border:1px solid var(--able-border); border-radius:10px; padding:14px; height:100%; }
        .jour-bloc.aujourdhui { border-color: var(--able-primary); box-shadow: 0 0 0 3px rgba(70,128,255,.12); }
        .jour-titre { font-weight:700; text-transform:capitalize; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center; }

        a:focus-visible, button:focus-visible { outline: 2px solid var(--able-primary); outline-offset: 2px; }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }
    </style>
    @stack('styles')
</head>
<body class="app sidebar-mini rtl">
    <header class="app-header">
        <a class="app-header__logo" href="{{ route('professeur.dashboard') }}">{{ $systemSettings->nom_court }}</a>
        <a class="app-sidebar__toggle fa fa-bars" href="#" data-toggle="sidebar" aria-label="Afficher ou masquer le menu"></a>
        @include('partials.raccourcis', ['raccourcis' => \App\Support\Raccourcis::pour(auth()->user())])
        <ul class="app-nav">
            <li class="dropdown">
                <a class="app-nav__item d-flex align-items-center" href="#" data-toggle="dropdown" aria-label="Menu profil">
                    @if ($photo)
                        <img class="app-header__user-avatar" src="{{ asset('storage/'.$photo) }}" alt="">
                    @else
                        <span class="app-header__user-initial">{{ $initiale }}</span>
                    @endif
                    <span class="d-none d-md-inline ml-2 text-white" style="font-size:14px">{{ $displayName }}</span>
                </a>
                <ul class="dropdown-menu settings-menu dropdown-menu-right border-0 shadow-lg">
                    <li class="dropdown-header">{{ $displayName }}</li>
                    <li><a class="dropdown-item" href="{{ route('professeur.profil') }}"><i class="fa fa-user fa-lg mr-2"></i> Mon profil</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="dropdown-item text-danger" type="submit"><i class="fa fa-sign-out fa-lg mr-2"></i> Déconnexion</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
         @include('partials.cloche-notifications')
    </header>

    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>

    <aside class="app-sidebar">
        <div class="app-sidebar__user">
            @if ($photo)
                <img class="app-sidebar__user-avatar mr-2" src="{{ asset('storage/'.$photo) }}" alt="">
            @else
                <span class="app-sidebar__user-initial mr-2">{{ $initiale }}</span>
            @endif
            <div>
                <p class="app-sidebar__user-name mb-0">{{ $displayName }}</p>
                <p class="app-sidebar__user-designation mb-0">Enseignant</p>
            </div>
        </div>
        <ul class="app-menu">
            <li class="menu-section">Mon espace</li>
            <li><a class="app-menu__item {{ request()->routeIs('professeur.dashboard') ? 'active' : '' }}" href="{{ route('professeur.dashboard') }}" @if (request()->routeIs('professeur.dashboard')) aria-current="page" @endif><i class="app-menu__icon fa fa-dashboard"></i><span class="app-menu__label">Vue générale</span></a></li>
            <li><a class="app-menu__item {{ request()->routeIs('professeur.planning') ? 'active' : '' }}" href="{{ route('professeur.planning') }}"><i class="app-menu__icon fa fa-calendar-o"></i><span class="app-menu__label">Mon planning</span></a></li>
            <li><a class="app-menu__item {{ request()->routeIs('professeur.cours', 'professeur.cours.show') ? 'active' : '' }}" href="{{ route('professeur.cours') }}"><i class="app-menu__icon fa fa-book"></i><span class="app-menu__label">Mes cours</span></a></li>
            <li><a class="app-menu__item {{ request()->routeIs('professeur.evaluations', 'professeur.notes') ? 'active' : '' }}" href="{{ route('professeur.evaluations') }}"><i class="app-menu__icon fa fa-pencil-square-o"></i><span class="app-menu__label">Évaluations &amp; notes</span></a></li>
            <li><a class="app-menu__item {{ request()->routeIs('professeur.eleves', 'professeur.eleves.show') ? 'active' : '' }}" href="{{ route('professeur.eleves') }}"><i class="app-menu__icon fa fa-users"></i><span class="app-menu__label">Mes élèves</span></a></li>
            <li><a class="app-menu__item {{ request()->routeIs('professeur.presences') ? 'active' : '' }}" href="{{ route('professeur.presences') }}"><i class="app-menu__icon fa fa-check-square-o"></i><span class="app-menu__label">Faire l’appel</span></a></li>
            <li class="menu-section">Compte</li>
            @php($messagesNonLus = \Illuminate\Support\Facades\Schema::hasTable('conversations') ? \App\Support\Messagerie::nonLus(auth()->user()) : 0)
            <li><a class="app-menu__item {{ request()->routeIs('messagerie.index') ? 'active' : '' }}" href="{{ route('messagerie.index') }}"><i class="app-menu__icon fa fa-comments"></i><span class="app-menu__label">Messagerie @if ($messagesNonLus > 0)<span class="badge badge-danger ml-1">{{ $messagesNonLus }}</span>@endif</span></a></li>
            <li><a class="app-menu__item {{ request()->routeIs('professeur.profil') ? 'active' : '' }}" href="{{ route('professeur.profil') }}"><i class="app-menu__icon fa fa-user"></i><span class="app-menu__label">Mon profil</span></a></li>
        </ul>
    </aside>

    <main class="app-content py-5">
        @include('partials.impersonation-banner')

        <div class="app-title border-0 bg-transparent pl-0 pt-0">
            <div>
                <h1 class="font-weight-bold" style="color: var(--able-text-main);">@yield('titre-page')</h1>
                <p class="text-muted">@yield('sous-titre')</p>
            </div>
            @hasSection('actions')
                <div>@yield('actions')</div>
            @endif
        </div>

        @if (! $professor)
            <div class="alert alert-info" role="status"><strong>Aucune fiche enseignant n’est reliée à ce compte.</strong> Demandez à l’administration de relier votre compte à votre fiche professeur pour afficher vos cours, vos élèves et votre planning.</div>
        @elseif (! ($schoolYear ?? null))
            <div class="alert alert-info" role="status"><strong>Aucune année scolaire en cours n’est définie.</strong> Vos affectations apparaîtront dès que l’administration aura activé l’année scolaire.</div>
        @endif

        @yield('content')
    </main>

    <script src="{{ asset('boostrap/css/js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('boostrap/css/js/popper.min.js') }}"></script>
    <script src="{{ asset('boostrap/css/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('boostrap/css/js/main.js') }}"></script>
    @stack('scripts')
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
