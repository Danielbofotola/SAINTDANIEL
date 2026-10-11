<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $systemSettings->nom_court }} - Administration scolaire</title>

    <!-- Google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons FontAwesome & Themify -->
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">

    <!-- DataTables Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

    <!-- Able Pro / Main Styling -->
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">

    <!-- Chart.js -->
    <script src="{{ asset('boostrap/css/js/plugins/chart.umd.js') }}"></script>

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

        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--able-bg);
            color: var(--able-text-main);
        }

        /* --- HEADER MODERNE & AVATAR --- */
        .app-header {
            background: #0f172a !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .app-header__logo {
            background-color: #020617 !important;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 18px;
        }

        .app-header__user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .app-header__user-initial {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--able-primary);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        /* --- SIDEBAR DESIGN ABLE PRO --- */
        .app-sidebar {
            background-color: #ffffff;
            border-right: 1px solid var(--able-border);
            box-shadow: none;
        }

        .app-sidebar__user {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid var(--able-border);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .app-sidebar__user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }

        .app-sidebar__user-initial {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--able-primary);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }

        .app-sidebar__user-name {
            color: var(--able-text-main);
            font-weight: 600;
        }

        .app-sidebar__user-designation {
            color: var(--able-text-muted);
            font-size: 12px;
        }

        .app-menu__item {
            border-radius: 8px;
            margin: 2px 12px;
            color: #475569;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .app-menu__item:hover, .app-menu__item.active {
            background: #eff6ff !important;
            color: var(--able-primary) !important;
            box-shadow: inset 4px 0 0 var(--able-primary); /* plus de décalage au survol */
        }

        .app-menu__item.active .app-menu__icon,
        .app-menu__item:hover .app-menu__icon {
            color: var(--able-primary) !important;
        }

        /* --- CARTES & WIDGETS STATISTIQUES --- */
        .stat-card {
            background: var(--able-card-bg);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--able-border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 16px;
            
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-icon.primary { background: #eff6ff; color: #2563eb; }
        .stat-icon.info { background: #f0fdf4; color: #16a34a; }
        .stat-icon.warning { background: #fffbeb; color: #d97706; }
        .stat-icon.danger { background: #fef2f2; color: #dc2626; }

        .stat-details h4 {
            font-size: 13px;
            font-weight: 600;
            color: var(--able-text-muted);
            margin-bottom: 4px;
        }

        .stat-details p {
            font-size: 22px;
            font-weight: 700;
            color: var(--able-text-main);
            margin: 0;
        }

        /* --- CARTES DE GRAPHIQUES ET TABLEAUX --- */
        .tile {
            background: var(--able-card-bg);
            border-radius: 12px;
            border: 1px solid var(--able-border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            padding: 24px;
            margin-bottom: 24px;
        }

        .tile-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--able-text-main);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* --- DATATABLE SUR-MESURE --- */
        .table {
            color: var(--able-text-main);
        }

        .table thead th {
            background-color: #f8fafc;
            border-bottom: 2px solid var(--able-border);
            color: var(--able-text-muted);
            font-size: 13px;
            font-weight: 600;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f5f9;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--able-border);
            border-radius: 6px;
            padding: 6px 12px;
            outline: none;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--able-primary);
            box-shadow: 0 0 0 3px rgba(70, 128, 255, 0.15);
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--able-border);
            border-radius: 6px;
            padding: 4px 8px;
        }

        .stat-icon.dark { background: #f1f5f9; color: #0f172a; }

        .chart-empty {
            position: absolute; inset: 0; margin: 0;
            display: none; align-items: center; justify-content: center;
            color: var(--able-text-muted); font-size: 14px;
        }

        a:focus-visible, button:focus-visible {
            outline: 2px solid var(--able-primary);
            outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }

        .menu-section { padding: 16px 24px 6px; font-size: 12px; font-weight: 600; color: var(--able-text-muted); list-style: none; }
        .stat-details small { display: block; margin-top: 2px; font-size: 12px; color: var(--able-text-muted); }
        .list-group-item { border-color: var(--able-border); padding: 14px 4px; }
    </style>
</head>
<body class="app sidebar-mini rtl">
    @php($initiale = mb_strtoupper(mb_substr(auth()->user()->prenom ?: auth()->user()->username, 0, 1)))
    @php($totalEleves = $statistiques['garcons'] + $statistiques['filles'])
    <!-- HEADER -->
    <header class="app-header">
        <a class="app-header__logo" href="{{ route('admin.dashboard') }}">{{ $systemSettings->nom_court }}</a>
        <a class="app-sidebar__toggle fa fa-bars" href="#" data-toggle="sidebar" aria-label="Afficher ou masquer le menu"></a>
        @include('partials.raccourcis', ['raccourcis' => \App\Support\Raccourcis::pour(auth()->user())])
        <ul class="app-nav">
            <li class="dropdown">
                <a class="app-nav__item d-flex align-items-center" href="#" data-toggle="dropdown" aria-label="Menu profil">
                    @if (auth()->user()->photo)
                        <img class="app-header__user-avatar" src="{{ asset('storage/'.auth()->user()->photo) }}" alt="Photo de profil">
                    @else
                        <span class="app-header__user-initial">{{ $initiale }}</span>
                    @endif
                    <span class="d-none d-md-inline ml-2 text-white" style="font-size:14px">{{ auth()->user()->username }}</span>
                </a>
                <ul class="dropdown-menu settings-menu dropdown-menu-right border-0 shadow-lg">
                    <li><a class="dropdown-item" href="{{ route('admin.profile.show') }}"><i class="fa fa-user fa-lg mr-2"></i> {{ auth()->user()->username }}</a></li>
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

    <!-- SIDEBAR -->
    <aside class="app-sidebar">
        <div class="app-sidebar__user">
            @if (auth()->user()->photo)
                <img class="app-sidebar__user-avatar mr-2" src="{{ asset('storage/'.auth()->user()->photo) }}" alt="Photo de profil">
            @else
                <span class="app-sidebar__user-initial mr-2">{{ $initiale }}</span>
            @endif
            <div>
                <p class="app-sidebar__user-name mb-0">{{ auth()->user()->username }}</p>
                <p class="app-sidebar__user-designation mb-0">Administration scolaire</p>
            </div>
        </div>
        <ul class="app-menu">
            <li class="menu-section">Pilotage</li>
            <li><a class="app-menu__item active" href="{{ route('admin.dashboard') }}"><i class="app-menu__icon fa fa-dashboard"></i><span class="app-menu__label">Tableau de bord</span></a></li>
            <li><a class="app-menu__item {{ request()->routeIs('admin.statistiques.index') ? 'active' : '' }}" href="{{ route('admin.statistiques.index') }}"><i class="app-menu__icon fa fa-pie-chart"></i><span class="app-menu__label">Statistiques</span></a></li>
            <li><a class="app-menu__item {{ request()->routeIs('admin.statistiques.evolution') ? 'active' : '' }}" href="{{ route('admin.statistiques.evolution') }}"><i class="app-menu__icon fa fa-line-chart"></i><span class="app-menu__label">Évolution</span></a></li>
            <li><a class="app-menu__item {{ request()->routeIs('admin.profile.online') ? 'active' : '' }}" href="{{ route('admin.profile.online') }}"><i class="app-menu__icon fa fa-plug"></i><span class="app-menu__label">Utilisateurs en ligne</span></a></li>
            <li><a class="app-menu__item {{ request()->routeIs('admin.annonces.index') ? 'active' : '' }}" href="{{ route('admin.annonces.index') }}"><i class="app-menu__icon fa fa-bullhorn"></i><span class="app-menu__label">Annonces &amp; notifications</span></a></li>
            @php($messagesNonLus = \Illuminate\Support\Facades\Schema::hasTable('conversations') ? \App\Support\Messagerie::nonLus(auth()->user()) : 0)
            <li><a class="app-menu__item {{ request()->routeIs('messagerie.index') ? 'active' : '' }}" href="{{ route('messagerie.index') }}"><i class="app-menu__icon fa fa-comments"></i><span class="app-menu__label">Messagerie @if ($messagesNonLus > 0)<span class="badge badge-danger ml-1">{{ $messagesNonLus }}</span>@endif</span></a></li>
            <li class="menu-section">Scolarité</li>
            <li class="treeview"><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-users"></i><span class="app-menu__label">Élèves</span><i class="treeview-indicator fa fa-angle-right"></i></a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item" href="{{ route('admin.eleves.index') }}"><i class="icon fa fa-list"></i> Liste des élèves</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.eleves.create') }}"><i class="icon fa fa-user-plus"></i> Inscrire un élève</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.registration.students') }}"><i class="icon fa fa-inbox"></i> Demandes d’inscription</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.registration.students.history') }}"><i class="icon fa fa-history"></i> Historique d'inscription</a></li>
                </ul>
            </li>
            <li class="treeview"><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-pencil-square-o"></i><span class="app-menu__label">Évaluations &amp; Bulletins</span><i class="treeview-indicator fa fa-angle-right"></i></a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item" href="{{ route('admin.periodes.index') }}"><i class="icon fa fa-calendar"></i> Périodes</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.evaluations.index') }}"><i class="icon fa fa-pencil"></i> Évaluations</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.notes.index') }}"><i class="icon fa fa-pencil-square-o"></i> Saisie des notes</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.bulletins.index') }}"><i class="icon fa fa-file-text-o"></i> Bulletins</a></li>
                </ul>
            </li>
            <li class="menu-section">Personnel enseignant</li>
            <li class="treeview"><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-user-md"></i><span class="app-menu__label">Professeurs</span><i class="treeview-indicator fa fa-angle-right"></i></a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item" href="{{ route('admin.professeurs.create') }}"><i class="icon fa fa-plus"></i> Créer un professeur</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.professeurs.index') }}"><i class="icon fa fa-list"></i> Liste des professeurs</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.affectations.index') }}"><i class="icon fa fa-book"></i> Cours &amp; affectation</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.emplois-du-temps.index') }}"><i class="icon fa fa-clock-o"></i> Horaire &amp; Emploi du temps</a></li>
                </ul>
            </li>
            <li class="menu-section">Organisation</li>
            <li class="treeview"><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-graduation-cap"></i><span class="app-menu__label">Classes et salles</span><i class="treeview-indicator fa fa-angle-right"></i></a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item" href="{{ route('admin.cycles.index') }}"><i class="icon fa fa-sitemap"></i> Cycles</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.classes.index') }}"><i class="icon fa fa-list"></i> Liste des classes</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.classes.create') }}"><i class="icon fa fa-plus"></i> Créer une classe</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.salles.index') }}"><i class="icon fa fa-list"></i> Liste des salles</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.salles.create') }}"><i class="icon fa fa-university"></i> Créer une salle</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.annees.index') }}"><i class="icon fa fa-calendar"></i> Années scolaires</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.sections.index') }}"><i class="icon fa fa-sitemap"></i> Sections</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.options.index') }}"><i class="icon fa fa-list-ul"></i> Options</a></li>
                </ul>
            </li>
            <li class="menu-section">Finances</li>
            <li class="treeview"><a class="app-menu__item" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-money"></i><span class="app-menu__label">Paiements et frais</span><i class="treeview-indicator fa fa-angle-right"></i></a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item" href="{{ route('admin.frais.index') }}"><i class="icon fa fa-tags"></i> Types de frais &amp; tarifs</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.paiements.index') }}"><i class="icon fa fa-credit-card"></i> Paiements</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.depenses.index') }}"><i class="icon fa fa-shopping-cart"></i> Dépenses</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.paies.index') }}"><i class="icon fa fa-money"></i> Paie &amp; salaires</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.archives.index') }}"><i class="icon fa fa-archive"></i> Clôture &amp; archives</a></li>
                    <li><a class="treeview-item" href="{{ route('admin.comptabilite.index') }}"><i class="icon fa fa-calculator"></i> Comptabilité OHADA</a></li>
                </ul>
            </li>
            <li class="menu-section">Accès</li>
            <li><a class="app-menu__item" href="{{ route('admin.registration.accounts') }}"><i class="app-menu__icon fa fa-user-plus"></i><span class="app-menu__label">Demandes de comptes</span></a></li>
            @php($messagesContactNonLus = \Illuminate\Support\Facades\Schema::hasTable('contact_messages') ? \App\Models\MessageContact::nouveaux()->count() : 0)
            <li><a class="app-menu__item {{ request()->routeIs('admin.messages-contact.*') ? 'active' : '' }}" href="{{ route('admin.messages-contact.index') }}"><i class="app-menu__icon fa fa-envelope-o"></i><span class="app-menu__label">Messages de contact @if ($messagesContactNonLus > 0)<span class="badge badge-danger ml-1">{{ $messagesContactNonLus }}</span>@endif</span></a></li>
        </ul>
    </aside>

    <!-- CONTENT -->
    <main class="app-content">
        <div class="app-title border-0 bg-transparent pl-0 pt-0 ">
            <div class="mt-5">
                <h1 class="font-weight-bold" style="color: var(--able-text-main);"><i class="fa fa-university mr-2"></i> Administration scolaire</h1>
                <p class="text-muted">Bienvenue, {{ auth()->user()->username }}. Suivez les effectifs, les classes et les inscriptions de l'établissement.</p>
                @if ($anneeEnCours)
                    <span class="badge badge-success"><i class="fa fa-calendar mr-1"></i> Année en cours : {{ $anneeEnCours->libelle }}</span>
                @else
                    <span class="badge badge-warning"><i class="fa fa-exclamation-triangle mr-1"></i> Aucune année en cours — <a href="{{ route('admin.annees.index') }}">en définir une</a></span>
                @endif
            </div>
            <div>
                <a href="{{ route('admin.eleves.create') }}" class="btn btn-primary btn-sm mr-2"><i class="fa fa-plus mr-1"></i> Inscrire un élève</a>
                <a href="{{ route('admin.classes.create') }}" class="btn btn-outline-primary btn-sm"><i class="fa fa-plus mr-1"></i> Créer une classe</a>
            </div>
        </div>

        <!-- STATS WIDGETS -->
        <div class="row mb-4">
            <div class="col-sm-6 col-lg mb-3">
                <div class="stat-card">
                    <div class="stat-icon dark"><i class="fa fa-users"></i></div>
                    <div class="stat-details">
                        <h4>Élèves inscrits</h4>
                        <p>{{ number_format($totalEleves, 0, ',', ' ') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg mb-3">
                <div class="stat-card">
                    <div class="stat-icon primary"><i class="fa fa-male"></i></div>
                    <div class="stat-details">
                        <h4>Garçons</h4>
                        <p>{{ number_format($statistiques['garcons'], 0, ',', ' ') }}</p>
                        <small>{{ $totalEleves ? round($statistiques['garcons'] / $totalEleves * 100) : 0 }} % des effectifs</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg mb-3">
                <div class="stat-card">
                    <div class="stat-icon info"><i class="fa fa-female"></i></div>
                    <div class="stat-details">
                        <h4>Filles</h4>
                        <p>{{ number_format($statistiques['filles'], 0, ',', ' ') }}</p>
                        <small>{{ $totalEleves ? round($statistiques['filles'] / $totalEleves * 100) : 0 }} % des effectifs</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg mb-3">
                <div class="stat-card">
                    <div class="stat-icon warning"><i class="fa fa-university"></i></div>
                    <div class="stat-details">
                        <h4>Classes</h4>
                        <p>{{ number_format($statistiques['classes'], 0, ',', ' ') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg mb-3">
                <div class="stat-card">
                    <div class="stat-icon danger"><i class="fa fa-user-md"></i></div>
                    <div class="stat-details">
                        <h4>Professeurs</h4>
                        <p>{{ number_format($statistiques['professeurs'], 0, ',', ' ') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- DEMANDES A TRAITER -->
        <div class="row">
            <div class="col-12">
                <div class="tile">
                    <h3 class="tile-title">Demandes à traiter</h3>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.registration.students') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-inbox mr-2 text-muted"></i> Demandes d’inscription d’élèves</span>
                            @isset($demandesInscriptions)<span class="badge badge-warning badge-pill">{{ $demandesInscriptions }}</span>@endisset
                        </a>
                        <a href="{{ route('admin.registration.accounts') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-user-plus mr-2 text-muted"></i> Demandes de comptes (parents, enseignants)</span>
                            @isset($demandesComptes)<span class="badge badge-warning badge-pill">{{ $demandesComptes }}</span>@endisset
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRAPHIQUES -->
        <div class="row">
            <div class="col-md-6">
                <div class="tile">
                    <h3 class="tile-title">Occupation des salles par classe</h3>
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="occupationChart" role="img" aria-label="Nombre d'élèves par classe"></canvas>
                        <p class="chart-empty" id="occupationEmpty">Aucune classe à afficher.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tile">
                    <h3 class="tile-title">Répartition par cycles</h3>
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="cyclesChart" role="img" aria-label="Répartition des élèves par cycle"></canvas>
                        <p class="chart-empty" id="cyclesEmpty">Aucun cycle à afficher.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- DATATABLE -->
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title mb-4">Liste des élèves inscrits</h3>
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless align-middle" id="elevesTable" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center">Photo</th>
                                    <th>Nom</th>
                                    <th>Post-nom</th>
                                    <th>Prénom</th>
                                    <th>Sexe</th>
                                    <th>Classe</th>
                                    <th>Cycle</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($eleves as $eleve)
                                    @php($inscription = $eleve->inscriptions->firstWhere('statut', 'actif') ?? $eleve->inscriptions->first())
                                    <tr>
                                        <td class="text-center align-middle">
                                            @if ($eleve->photo)
                                                <img src="{{ asset('storage/'.$eleve->photo) }}" alt="" loading="lazy" width="38" height="38" class="rounded-circle" style="object-fit:cover">
                                            @else
                                                <i class="fa fa-user-circle fa-2x text-secondary"></i>
                                            @endif
                                        </td>
                                        <td class="align-middle font-weight-bold">{{ $eleve->nom }}</td>
                                        <td class="align-middle">{{ $eleve->postnom }}</td>
                                        <td class="align-middle">{{ $eleve->prenom }}</td>
                                        <td class="align-middle">
                                            <span class="badge {{ $eleve->sexe === 'M' ? 'badge-primary' : 'badge-info' }} px-2 py-1">
                                                {{ $eleve->sexe === 'M' ? 'Garçon' : 'Fille' }}
                                            </span>
                                        </td>
                                        <td class="align-middle">{{ $inscription?->classe?->nom ?? 'Non affecté' }}</td>
                                        <td class="align-middle">{{ $inscription?->classe?->cycle?->nom ?? '—' }}</td>
                                        <td class="text-center align-middle text-nowrap">
                                            <a href="{{ route('admin.eleves.show', $eleve) }}" class="btn btn-sm btn-light text-info" title="Voir le profil" aria-label="Voir le profil"><i class="fa fa-user"></i></a>
                                            <button type="button" class="btn btn-sm btn-light text-warning" title="Détails rapides" aria-label="Détails rapides"
                                                data-toggle="modal" data-target="#studentDetailsModal"
                                                data-student-name="{{ trim($eleve->nom.' '.$eleve->postnom.' '.$eleve->prenom) }}"
                                                data-student-matricule="{{ $eleve->matricule }}"
                                                data-student-sex="{{ $eleve->sexe === 'M' ? 'Garçon' : 'Fille' }}"
                                                data-student-birth="{{ $eleve->date_naissance?->format('d/m/Y') ?? 'Non renseignée' }}"
                                                data-student-class="{{ $inscription?->classe?->nom ?? 'Non affecté' }}"
                                                data-student-cycle="{{ $inscription?->classe?->cycle?->nom ?? 'Non renseigné' }}"
                                                data-student-photo="{{ $eleve->photo ? asset('storage/'.$eleve->photo) : '' }}"
                                                data-student-edit-url="{{ route('admin.eleves.edit', $eleve) }}"
                                                data-student-url="{{ route('admin.eleves.show', $eleve) }}"><i class="fa fa-eye"></i></button>
                                            <a href="{{ route('admin.eleves.edit', $eleve) }}" class="btn btn-sm btn-light text-danger" title="Modifier le dossier" aria-label="Modifier le dossier"><i class="fa fa-pencil"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- Tableau vide : DataTables affiche son message « emptyTable ».
                                         Un <td colspan> dans le corps ferait compter 1 cellule au lieu de 8
                                         et déclencherait « Incorrect column count » (tn/18). --}}
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL ELEVE -->
    <div class="modal fade" id="studentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="studentDetailsTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold" id="studentDetailsTitle">Détails de l’élève</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3">
                        <img id="studentDetailsPhoto" src="" alt="Photo de l’élève" width="80" height="80" class="rounded-circle mr-3" style="display:none;object-fit:cover">
                        <span id="studentDetailsPlaceholder" class="mr-3 text-secondary" aria-hidden="true"><i class="fa fa-user-circle fa-4x"></i></span>
                        <div>
                            <h4 class="mb-1 font-weight-bold" id="studentDetailsName"></h4>
                            <span class="badge badge-primary" id="studentDetailsMatricule"></span>
                        </div>
                    </div>
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted font-weight-normal">Sexe</dt><dd class="col-7 font-weight-bold" id="studentDetailsSex"></dd>
                        <dt class="col-5 text-muted font-weight-normal">Naissance</dt><dd class="col-7 font-weight-bold" id="studentDetailsBirth"></dd>
                        <dt class="col-5 text-muted font-weight-normal">Classe</dt><dd class="col-7 font-weight-bold" id="studentDetailsClass"></dd>
                        <dt class="col-5 text-muted font-weight-normal">Cycle</dt><dd class="col-7 font-weight-bold" id="studentDetailsCycle"></dd>
                    </dl>
                </div>
                <div class="modal-footer border-0 bg-light" style="border-radius: 0 0 12px 12px;">
                    <a class="btn btn-warning btn-sm" id="studentDetailsEditProfile" href="#"><i class="fa fa-pencil"></i> Modifier</a>
                    <a class="btn btn-info btn-sm" id="studentDetailsFullProfile" href="#"><i class="fa fa-user"></i> Fiche complète</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="{{ asset('boostrap/css/js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('boostrap/css/js/popper.min.js') }}"></script>
    <script src="{{ asset('boostrap/css/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('boostrap/css/js/main.js') }}"></script>

    <!-- DATATABLES SCRIPTS -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            // Modal Détails Élève
            $('#studentDetailsModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const modal = $(this);

                modal.find('#studentDetailsName').text(button.data('studentName'));
                modal.find('#studentDetailsMatricule').text(button.data('studentMatricule'));
                modal.find('#studentDetailsSex').text(button.data('studentSex'));
                modal.find('#studentDetailsBirth').text(button.data('studentBirth'));
                modal.find('#studentDetailsClass').text(button.data('studentClass'));
                modal.find('#studentDetailsCycle').text(button.data('studentCycle'));

                const photoUrl = button.data('studentPhoto');
                if (photoUrl) {
                    modal.find('#studentDetailsPhoto').attr('src', photoUrl).show();
                    modal.find('#studentDetailsPlaceholder').hide();
                } else {
                    modal.find('#studentDetailsPhoto').hide();
                    modal.find('#studentDetailsPlaceholder').show();
                }

                modal.find('#studentDetailsEditProfile').attr('href', button.data('studentEditUrl'));
                modal.find('#studentDetailsFullProfile').attr('href', button.data('studentUrl'));
            });

            // DATA TABLES
            if ($.fn.DataTable) {
                $('#elevesTable').DataTable({
                    language: {
                        search: "Rechercher :",
                        lengthMenu: "Afficher _MENU_ éléments",
                        info: "Affichage de _START_ à _END_ sur _TOTAL_ élèves",
                        infoEmpty: "Aucun élément disponible",
                        infoFiltered: "(filtré de _MAX_ éléments au total)",
                        zeroRecords: "Aucun élève trouvé",
                        emptyTable: "Aucun élève inscrit pour le moment.",
                        paginate: {
                            first: "Premier",
                            previous: "Précédent",
                            next: "Suivant",
                            last: "Dernier"
                        }
                    },
                    columnDefs: [{ orderable: false, targets: [0, 7] }],
                    pageLength: 10,
                    order: [[1, 'asc']]
                });
            }

            // CHARTS
            const occupationLabels = @json($occupationSalles->map(fn ($classe) => $classe->nom . ($classe->salle ? ' - ' . $classe->salle->nom : '')));
            const occupationData = @json($occupationSalles->pluck('eleves_count'));
            const cycleLabels = @json($repartitionCycles->pluck('nom'));
            const cycleData = @json($repartitionCycles->pluck('total'));

const hasData = (arr) => Object.values(arr).some(v => Number(v) > 0);

            if (typeof Chart !== 'undefined') {
                Chart.defaults.font.family = "'Public Sans', sans-serif";
                Chart.defaults.color = '#64748b';

                // Occupation : colonnes parallèles, une couleur par classe
                const ctxBar = document.getElementById('occupationChart');
                if (ctxBar && hasData(occupationData)) {
                    const base = [[255,99,132],[255,159,64],[255,205,86],[75,192,192],[54,162,235],[153,102,255],[201,203,207]];
                    const n = Object.keys(occupationData).length;
                    const fills = Array.from({ length: n }, (_, i) => `rgba(${base[i % base.length].join(',')}, 0.2)`);
                    const borders = Array.from({ length: n }, (_, i) => `rgb(${base[i % base.length].join(',')})`);

                    // Beaucoup de classes : le graphique s'élargit et défile horizontalement
                    ctxBar.parentElement.style.minWidth = (n * 40) + 'px';
                    ctxBar.parentElement.parentElement.style.overflowX = 'auto';

                    new Chart(ctxBar, {
                        type: 'bar',
                        data: {
                            labels: occupationLabels,
                            datasets: [{
                                data: occupationData,
                                backgroundColor: fills,
                                borderColor: borders,
                                borderWidth: 1,
                                borderRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: { callbacks: { label: c => c.parsed.y + (c.parsed.y > 1 ? ' élèves' : ' élève') } }
                            },
                            scales: {
                                x: { grid: { display: false }, ticks: { display: false } }, // libellés du bas masqués (nom de la classe visible au survol)
                                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }
                            }
                        }
                    });
                } else {
                    document.getElementById('occupationEmpty').style.display = 'flex';
                }

                // Cycles : barres horizontales avec pourcentages
                const ctxPie = document.getElementById('cyclesChart');
                if (ctxPie && hasData(cycleData)) {
                    const totalCycles = Object.values(cycleData).reduce((t, v) => t + Number(v), 0);
                    new Chart(ctxPie, {
                        type: 'bar',
                        data: {
                            labels: cycleLabels,
                            datasets: [{
                                data: cycleData,
                                backgroundColor: ['#4680ff', '#2ed8b6', '#ffb64d', '#ff5370', '#00bcd4'],
                                borderRadius: 4,
                                maxBarThickness: 28
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: { callbacks: { label: c => ` ${c.parsed.x} élève(s) (${Math.round(c.parsed.x / totalCycles * 100)} %)` } }
                            },
                            scales: {
                                x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                                y: { grid: { display: false } }
                            }
                        }
                    });
                } else {
                    document.getElementById('cyclesEmpty').style.display = 'flex';
                }
            }
        });
    </script>
</body>
</html>