@php
    $etablissement = $systemSettings->nom_etablissement ?: 'Établissement scolaire';
    $sigle = $systemSettings->nom_court ?: $etablissement;
    $navigation = [
        ['route' => 'site.accueil', 'libelle' => 'Accueil'],
        ['route' => 'site.inscriptions', 'libelle' => 'Inscriptions'],
        ['route' => 'site.apropos', 'libelle' => 'À propos'],
        ['route' => 'site.contact', 'libelle' => 'Contact'],
    ];
@endphp
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="@yield('description', $etablissement . ' : informations, inscriptions en ligne et contact.')">
        <title>@yield('titre', 'Accueil') · {{ $etablissement }}</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('site-assets/favicon.ico') }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('site-assets/styles.css') }}">
        <style>
            body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; }
            .scroll-to-top { display: none; }
            .site-navbar .nav-link.active { color: #1e30f3; font-weight: 700; }
            .section-titre { font-weight: 800; letter-spacing: -.02em; }
            .step-numero { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px;
                color: #fff; background: linear-gradient(135deg, #1e30f3 0%, #e21e80 100%); border-radius: 50%; font-weight: 700; }
            .carte-douce { border: 1px solid rgba(15, 23, 42, .07); box-shadow: 0 14px 40px rgba(15, 23, 42, .06); }
            /* Formulaires publics (demandes, connexion) : mêmes teintes que la vitrine. */
            .form-panel { border: 1px solid rgba(15, 23, 42, .07); box-shadow: 0 14px 40px rgba(15, 23, 42, .06); }
            .form-panel .form-label { color: #334155; font-size: .8rem; font-weight: 700; }
            .form-panel .form-control, .form-panel .form-select { min-height: 44px; }
            .form-panel .form-control:focus, .form-panel .form-select:focus { border-color: #1e30f3; box-shadow: 0 0 0 .2rem rgba(30, 48, 243, .12); }
            .section-titre-form { margin: 4px 0 16px; padding-bottom: 9px; border-bottom: 1px solid #e2e8f0; color: #1e30f3;
                font-size: .8rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
            footer a { color: #4b5563; text-decoration: none; }
            footer a:hover { color: #1e30f3; text-decoration: underline; }
        </style>
        @stack('styles')
    </head>
    <body class="d-flex flex-column h-100 bg-white">
        <main class="flex-shrink-0">
            <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm site-navbar">
                <div class="container px-4 px-lg-5">
                    <a class="navbar-brand d-flex flex-column lh-1" href="{{ route('site.accueil') }}">
                        <span class="fw-bolder text-primary">{{ $sigle }}</span>
                        <span class="text-muted fw-light d-none d-sm-block" style="font-size: .7rem;">{{ $etablissement }}</span>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navigationPublique"
                            aria-controls="navigationPublique" aria-expanded="false" aria-label="Afficher le menu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navigationPublique">
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 small fw-bolder align-items-lg-center">
                            @foreach ($navigation as $lien)
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs($lien['route']) ? 'active' : '' }}"
                                       href="{{ route($lien['route']) }}" @if (request()->routeIs($lien['route'])) aria-current="page" @endif>{{ $lien['libelle'] }}</a>
                                </li>
                            @endforeach
                            <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                                @if (request()->routeIs('login', 'login.pin', 'login.*'))
                                    <a class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-bolder" href="{{ route('site.accueil') }}">
                                        <i class="bi bi-house-door me-1"></i> Accueil du site
                                    </a>
                                @else
                                    <a class="btn btn-primary btn-sm px-3 rounded-pill fw-bolder" href="{{ route('login') }}">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Espace connexion
                                    </a>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            @yield('contenu')
        </main>

        <footer class="bg-light py-5 mt-auto">
            <div class="container px-4 px-lg-5">
                <div class="row gx-5">
                    <div class="col-lg-5 mb-4 mb-lg-0">
                        <div class="fw-bolder text-primary mb-2">{{ $sigle }}</div>
                        <p class="text-muted small mb-2">{{ $etablissement }}</p>
                        @if ($systemSettings->adresse)
                            <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-1"></i>{{ $systemSettings->adresse }}</p>
                        @endif
                    </div>
                    <div class="col-lg-3 col-6 mb-4 mb-lg-0">
                        <div class="small fw-bolder mb-2 text-uppercase text-muted">Navigation</div>
                        <ul class="list-unstyled small mb-0">
                            @foreach ($navigation as $lien)
                                <li class="mb-1"><a href="{{ route($lien['route']) }}">{{ $lien['libelle'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-lg-4 col-6">
                        <div class="small fw-bolder mb-2 text-uppercase text-muted">Démarches &amp; contact</div>
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-1"><a href="{{ route('registration.student.create') }}">Demander une inscription</a></li>
                            <li class="mb-1"><a href="{{ route('registration.account.create') }}">Demander un compte</a></li>
                            <li class="mb-1"><a href="{{ route('login') }}">Se connecter</a></li>
                            <li class="mb-1"><a href="{{ route('login.pin') }}">Connexion par PIN</a></li>
                            @if ($systemSettings->telephone)
                                <li class="mb-1"><i class="bi bi-telephone me-1"></i>{{ $systemSettings->telephone }}</li>
                            @endif
                            @if ($systemSettings->email)
                                <li class="mb-1"><i class="bi bi-envelope me-1"></i><a href="mailto:{{ $systemSettings->email }}">{{ $systemSettings->email }}</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <hr class="my-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between small text-muted">
                    <span>&copy; {{ date('Y') }} {{ $etablissement }} — Tous droits réservés.</span>
                    <span>Année scolaire : {{ $anneeEnCours?->libelle ?? 'non ouverte' }}</span>
                </div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
