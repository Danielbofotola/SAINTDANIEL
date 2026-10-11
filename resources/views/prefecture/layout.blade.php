<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titre', 'Espace préfecture') — {{ $systemSettings->nom_court }}</title>
    <link rel="icon" href="{{ asset('azzara/img/icon.ico') }}" type="image/x-icon" />

    {{-- Fonts & icônes (Flaticon + Font Awesome 5) --}}
    <script src="{{ asset('azzara/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: { families: ['Open+Sans:300,400,600,700'] },
            custom: {
                families: ['Flaticon', 'Font Awesome 5 Solid', 'Font Awesome 5 Regular', 'Font Awesome 5 Brands'],
                urls: ['{{ asset('azzara/css/fonts.css') }}']
            },
            active: function () { sessionStorage.fonts = true; }
        });
    </script>

    <link rel="stylesheet" href="{{ asset('azzara/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('azzara/css/azzara.min.css') }}">
    <style>
        /* DataTables (bundle local Azzara) : contrôles alignés sur le thème. */
        .dataTables_wrapper .dataTables_filter { padding-bottom: 8px; }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d9d9d9; border-radius: 4px; padding: 4px 8px; height: auto; }
        .dataTables_wrapper .dataTables_info { padding-top: 10px; font-size: .82rem; color: #6c757d; }
        .dataTables_wrapper .pagination { margin: 0; }
        .dataTables_wrapper .table-responsive { overflow: visible; }
        table.dataTable thead th { border-bottom: 2px solid #dee2e6; white-space: nowrap; }

        /* Menu latéral Desktop */
        body.menu-reduit .sidebar { transform: translateX(-100%); }
        body.menu-reduit .main-panel { margin-left: 0 !important; width: 100%; }
        .toggle-sidebar { background: none; border: 0; padding: 6px 10px; cursor: pointer; color: inherit; }

        /* --- STYLES MOBILE ET TIROIR LATÉRAL (RESPONSIVE) --- */
        @media (max-width: 991.98px) {
            /* Sidebar cachée à gauche par défaut sur mobile */
            body .sidebar {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                bottom: 0 !important;
                transform: translateX(-100%) !important;
                transition: transform .3s ease-in-out !important;
                z-index: 1050 !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
                box-shadow: 2px 0 12px rgba(0, 0, 0, 0.25) !important;
            }

            /* Affichage du tiroir quand activé */
            body.nav_open .sidebar {
                transform: translateX(0) !important;
            }

            /* Main panel prend toute la largeur */
            body .main-panel { 
                margin-left: 0 !important; 
                width: 100% !important; 
            }

            /* Overlay semi-transparent en arrière-plan */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                transition: opacity .3s ease;
            }

            body.nav_open .sidebar-overlay {
                display: block !important;
            }
        }

        /* Mobile : ajustements en-tête et filtres */
        @media (max-width: 767.98px) {
            .page-header { flex-wrap: wrap; gap: 10px; }
            .form-inline { display: flex; flex-wrap: wrap; gap: 6px; width: 100%; }
            .form-inline .form-control { width: 100%; margin-right: 0 !important; }
            .form-inline .btn { width: 100%; }
            .btn-group-page-header { display: flex; flex-wrap: wrap; gap: 6px; }
            .page-title { font-size: 1.15rem; }
            .card-title, .section-titre { font-size: .95rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $displayName = trim(($user->prenom ?? '').' '.($user->nom ?? '')) ?: $user->username;
    try {
        $nonLues = $user->unreadNotifications()->count();
    } catch (\Throwable) {
        $nonLues = 0;
    }
    try {
        $aSuivreAujourdhui = \App\Models\Presence::query()
            ->where('date_seance', now()->toDateString())
            ->whereIn('statut_presence', ['absent', 'retard'])
            ->count();
    } catch (\Throwable) {
        $aSuivreAujourdhui = 0;
    }
    $initial = mb_strtoupper(mb_substr($displayName, 0, 1));
@endphp

<!-- Overlay pour fermeture mobile -->
<div class="sidebar-overlay"></div>

<div class="wrapper">
    <div class="main-header" data-background-color="purple">
        <div class="logo-header">
            <a href="{{ route('prefecture.dashboard') }}" class="logo">
                <img src="{{ asset('azzara/img/logoazzara.svg') }}" alt="{{ $systemSettings->nom_court }}" class="navbar-brand">
            </a>
            <button class="navbar-toggler sidenav-toggler ml-auto" type="button" aria-label="Afficher ou masquer le menu">
                <span class="navbar-toggler-icon"><i class="fa fa-bars"></i></span>
            </button>
            <button class="topbar-toggler more"><i class="fa fa-ellipsis-v"></i></button>
            <div class="navbar-minimize">
                <button class="btn btn-minimize btn-rounded"><i class="fa fa-bars"></i></button>
            </div>
        </div>

        <nav class="navbar navbar-header navbar-expand-lg">
            <div class="container-fluid">
                <button class="toggle-sidebar d-none d-lg-inline-block" type="button" title="Afficher / masquer le menu">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                    <li class="nav-item">
                        <span class="nav-link" title="Année scolaire">
                            <i class="far fa-calendar-alt"></i>
                            <span class="d-none d-md-inline">{{ $schoolYear?->libelle ?? 'Année non définie' }}</span>
                        </span>
                    </li>
                    <li class="nav-item dropdown hidden-caret">
                        <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#" aria-expanded="false">
                            <div class="avatar-sm">
                                @if ($user->photo)
                                    <img src="{{ asset('storage/'.$user->photo) }}" alt="" class="avatar-img rounded-circle">
                                @else
                                    <span class="avatar-title rounded-circle bg-primary">{{ $initial }}</span>
                                @endif
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-user animated fadeIn">
                            <li>
                                <div class="user-box">
                                    <div class="avatar-lg">
                                        @if ($user->photo)
                                            <img src="{{ asset('storage/'.$user->photo) }}" alt="" class="avatar-img rounded">
                                        @else
                                            <span class="avatar-title rounded bg-primary">{{ $initial }}</span>
                                        @endif
                                    </div>
                                    <div class="u-text">
                                        <h4>{{ $displayName }}</h4>
                                        <p class="text-muted">{{ $user->email }}</p>
                                        <a href="{{ route('prefecture.profil') }}" class="btn btn-rounded btn-danger btn-sm">Mon profil</a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('prefecture.profil') }}">Mon profil</a>
                                <a class="dropdown-item" href="{{ route('notifications.index') }}">Notifications</a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit">Déconnexion</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    {{-- Menu latéral --}}
    <div class="sidebar">
        <div class="sidebar-background"></div>
        <div class="sidebar-wrapper scrollbar-inner">
            <div class="sidebar-content">
                <div class="user">
                    <div class="avatar-sm float-left mr-2">
                        @if ($user->photo)
                            <img src="{{ asset('storage/'.$user->photo) }}" alt="" class="avatar-img rounded-circle">
                        @else
                            <span class="avatar-title rounded-circle bg-primary">{{ $initial }}</span>
                        @endif
                    </div>
                    <div class="info">
                        <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                            <span>
                                {{ $displayName }}
                                <span class="user-level">Préfecture</span>
                                <span class="caret"></span>
                            </span>
                        </a>
                        <div class="clearfix"></div>
                        <div class="collapse in" id="collapseExample">
                            <ul class="nav">
                                <li><a href="{{ route('prefecture.profil') }}"><span class="link-collapse">Mon profil</span></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <ul class="nav">
                    <li class="nav-item {{ request()->routeIs('prefecture.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('prefecture.dashboard') }}">
                            <i class="fas fa-home"></i>
                            <p>Tableau de bord</p>
                        </a>
                    </li>
                    <li class="nav-section">
                        <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                        <h4 class="text-section">Supervision</h4>
                    </li>
                    <li class="nav-item {{ request()->routeIs('prefecture.effectifs') ? 'active' : '' }}">
                        <a href="{{ route('prefecture.effectifs') }}">
                            <i class="fas fa-chart-bar"></i>
                            <p>Effectifs par cycle</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('prefecture.classes') ? 'active' : '' }}">
                        <a href="{{ route('prefecture.classes') }}">
                            <i class="fas fa-table"></i>
                            <p>Classes</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('prefecture.personnel') ? 'active' : '' }}">
                        <a href="{{ route('prefecture.personnel') }}">
                            <i class="fas fa-users"></i>
                            <p>Personnel</p>
                        </a>
                    </li>
                    <li class="nav-section">
                        <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                        <h4 class="text-section">Vie scolaire</h4>
                    </li>
                    <li class="nav-item {{ request()->routeIs('prefecture.presences') ? 'active' : '' }}">
                        <a href="{{ route('prefecture.presences') }}">
                            <i class="fas fa-user-clock"></i>
                            <p>Présence &amp; ponctualité
                                @if ($aSuivreAujourdhui > 0)
                                    <span class="badge badge-danger ml-1"
                                          title="{{ $aSuivreAujourdhui }} absence(s) ou retard(s) aujourd'hui">{{ $aSuivreAujourdhui }}</span>
                                @endif
                            </p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('prefecture.horaires') ? 'active' : '' }}">
                        <a href="{{ route('prefecture.horaires') }}">
                            <i class="far fa-clock"></i>
                            <p>Horaire des cours</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('prefecture.evaluations') ? 'active' : '' }}">
                        <a href="{{ route('prefecture.evaluations') }}">
                            <i class="fas fa-clipboard-check"></i>
                            <p>Évaluations</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('prefecture.eleves*') ? 'active' : '' }}">
                        <a href="{{ route('prefecture.eleves') }}">
                            <i class="fas fa-folder-open"></i>
                            <p>Dossiers élèves</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('prefecture.tuteurs') ? 'active' : '' }}">
                        <a href="{{ route('prefecture.tuteurs') }}">
                            <i class="fas fa-user-friends"></i>
                            <p>Tuteurs &amp; parents</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('prefecture.bulletins*') ? 'active' : '' }}">
                        <a href="{{ route('prefecture.bulletins') }}">
                            <i class="fas fa-file-alt"></i>
                            <p>Résultats &amp; bulletins</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('prefecture.periodes*') ? 'active' : '' }}">
                        <a href="{{ route('prefecture.periodes') }}">
                            <i class="far fa-calendar-check"></i>
                            <p>Périodes</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        <a href="{{ route('notifications.index') }}">
                            <i class="far fa-bell"></i>
                            <p>Notifications
                                @if ($nonLues > 0)
                                    <span class="badge badge-primary ml-1"
                                          title="{{ $nonLues }} notification(s) non lue(s)">{{ $nonLues }}</span>
                                @endif
                            </p>
                        </a>
                    </li>
                    <li class="nav-section">
                        <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                        <h4 class="text-section">Mon compte</h4>
                    </li>
                    <li class="nav-item {{ request()->routeIs('prefecture.profil') ? 'active' : '' }}">
                        <a href="{{ route('prefecture.profil') }}">
                            <i class="far fa-user"></i>
                            <p>Mon profil</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Contenu --}}
    <div class="main-panel">
        <div class="content">
            <div class="page-inner">
                @include('partials.impersonation-banner')

                <div class="page-header">
                    <h4 class="page-title">@yield('titre', 'Espace préfecture')</h4>
                    <div class="ml-auto">@yield('actions')</div>
                </div>

                @yield('contenu')
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="{{ asset('azzara/js/core/jquery.3.2.1.min.js') }}"></script>
<script src="{{ asset('azzara/js/core/popper.min.js') }}"></script>
<script src="{{ asset('azzara/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('azzara/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
<script src="{{ asset('azzara/js/plugin/chart.js/chart.min.js') }}"></script>
<script src="{{ asset('azzara/js/ready.min.js') }}"></script>
<script src="{{ asset('azzara/js/plugin/datatables/datatables.min.js') }}"></script>

<script>
    (function () {
        var corps = document.body;
        var estMobile = function () { return window.innerWidth < 992; };

        // Gestion de l'ouverture/fermeture du menu
        var basculerMenu = function (e) {
            if (e) { e.preventDefault(); }
            
            if (estMobile()) {
                corps.classList.toggle('nav_open');
            } else {
                var replie = corps.classList.toggle('sidebar_minimize');
                corps.classList.toggle('menu-reduit', replie);
                try {
                    sessionStorage.setItem('prefecture-menu', replie ? 'reduit' : 'ouvert');
                } catch (err) {}
            }
        };

        var fermerMenuMobile = function () {
            corps.classList.remove('nav_open');
        };

        // Événements sur les boutons de bascule (Hamburger mobile & Desktop)
        var boutons = document.querySelectorAll('.toggle-sidebar, .btn-minimize, .sidenav-toggler');
        boutons.forEach(function (btn) {
            btn.addEventListener('click', basculerMenu);
        });

        // Fermeture en cliquant sur l'overlay sombre sur mobile
        var overlay = document.querySelector('.sidebar-overlay');
        if (overlay) {
            overlay.addEventListener('click', fermerMenuMobile);
        }

        // Fermeture sur la touche Échap
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && estMobile()) {
                fermerMenuMobile();
            }
        });

        // Fermeture au clic sur un lien du menu sur mobile
        document.querySelectorAll('.sidebar a').forEach(function (lien) {
            lien.addEventListener('click', function () {
                if (estMobile()) {
                    fermerMenuMobile();
                }
            });
        });

        // Réinitialiser les classes en cas de redimensionnement de la fenêtre
        window.addEventListener('resize', function () {
            if (!estMobile()) {
                fermerMenuMobile();
            }
        });

        // Mémorisation de l'état Desktop
        try {
            if (sessionStorage.getItem('prefecture-menu') === 'reduit' && !estMobile()) {
                corps.classList.add('sidebar_minimize', 'menu-reduit');
            }
        } catch (e) {}
    })();
</script>

@stack('scripts')
<script>
    jQuery(function ($) {
        if (! $.fn.DataTable) { return; }

        $('table.table').each(function () {
            var $tableau = $(this);

            if ($tableau.data('datatable') === 'off') { return; }
            if ($tableau.find('thead').length === 0) { return; }
            if ($.fn.DataTable.isDataTable(this)) { return; }

            $tableau.DataTable({
                language: {
                    search: 'Rechercher :',
                    lengthMenu: 'Afficher _MENU_ lignes',
                    info: '_START_ à _END_ sur _TOTAL_ lignes',
                    infoEmpty: 'Aucune ligne',
                    zeroRecords: 'Aucun résultat',
                    paginate: { previous: 'Précédent', next: 'Suivant' }
                },
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Tout']],
                order: [],
                autoWidth: false,
                paging: $tableau.data('datatable-paging') !== 'off',
                columnDefs: [{ orderable: false, targets: 'no-sort' }]
            });
        });
    });
</script>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>