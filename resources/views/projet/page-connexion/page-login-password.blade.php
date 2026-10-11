@extends('site::layout')

@section('titre', 'Connexion')
@section('description', 'Espace personnel de ' . $systemSettings->nom_etablissement . ' : connexion par identifiant, e-mail ou code PIN.')

@section('contenu')
    <section class="py-5 bg-light">
        <div class="container px-4 px-lg-5 py-4">
            <div class="row gx-0 justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="card border-0 rounded-4 overflow-hidden form-panel">
                        <div class="row g-0">
                            <div class="col-lg-5 bg-gradient-primary-to-secondary text-white">
                                <div class="p-4 p-lg-5 h-100 d-flex flex-column">
                                    <div class="mb-4">
                                        <div class="small text-uppercase opacity-75">{{ $systemSettings->nom_court }}</div>
                                        <h1 class="h3 fw-bolder mt-2 mb-3">Espace personnel</h1>
                                        <p class="opacity-75 mb-0">
                                            Notes, bulletins, présences, paiements et messagerie : retrouvez ici les outils
                                            réservés au personnel, aux élèves et aux parents de l’établissement.
                                        </p>
                                    </div>
                                    <ul class="list-unstyled small opacity-75 mb-4">
                                        <li class="mb-2"><i class="bi bi-shield-lock me-2"></i>Accès nominatif, vérifié à chaque connexion.</li>
                                        <li class="mb-2"><i class="bi bi-people me-2"></i>Chaque rôle voit uniquement son espace de travail.</li>
                                        <li><i class="bi bi-phone me-2"></i>Connexion rapide possible par code PIN.</li>
                                    </ul>
                                    <a class="btn btn-light rounded-pill fw-bolder mt-auto align-self-start" href="{{ route('site.accueil') }}">
                                        <i class="bi bi-arrow-left me-1"></i> Retour à l’accueil du site
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="p-4 p-lg-5">
                                    <h2 class="h4 fw-bolder mb-1">CONNEXION</h2>
                                    <p class="text-muted small mb-4">Identifiant, e-mail ou connexion par code PIN.</p>

                                    @if (session('success'))
                                        <div class="alert alert-success rounded-4" role="status"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>
                                    @endif
                                    {{-- Les erreurs (identifiants, compte désactivé) sont affichées par le
                                         bandeau global et par le retour de champ ci-dessous. --}}
                                    <form method="POST" action="{{ route('login.store') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label" for="login">Identifiant ou e-mail</label>
                                            <input id="login" class="form-control @error('login') is-invalid @enderror" type="text" name="login"
                                                   value="{{ old('login') }}" required autofocus autocomplete="username">
                                            @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="password">Mot de passe</label>
                                            <input id="password" class="form-control @error('password') is-invalid @enderror" type="password" name="password"
                                                   required autocomplete="current-password">
                                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                                                <label class="form-check-label small" for="remember">Se souvenir de moi</label>
                                            </div>
                                            <a class="small text-decoration-none fw-bolder" href="{{ route('login.pin') }}">
                                                <i class="bi bi-grid-3x3-gap me-1"></i>Connexion par PIN
                                            </a>
                                        </div>
                                        <button class="btn btn-primary btn-lg w-100 rounded-pill fw-bolder" type="submit">
                                            <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
                                        </button>
                                    </form>

                                    <hr class="my-4">
                                    <p class="small text-muted mb-2">Pas encore de compte&nbsp;?</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a class="btn btn-outline-primary rounded-pill fw-bolder btn-sm px-3" href="{{ route('registration.student.create') }}">
                                            <i class="bi bi-person-plus me-1"></i> Demander une inscription
                                        </a>
                                        <a class="btn btn-outline-secondary rounded-pill fw-bolder btn-sm px-3" href="{{ route('registration.account.create') }}">
                                            <i class="bi bi-person-badge me-1"></i> Demander un compte
                                        </a>
                                    </div>

                                    @if ($systemSettings->telephone || $systemSettings->email || $systemSettings->adresse)
                                        <hr class="my-4">
                                        <ul class="list-unstyled small text-muted mb-0">
                                            @if ($systemSettings->telephone)<li><i class="bi bi-telephone me-2"></i>{{ $systemSettings->telephone }}</li>@endif
                                            @if ($systemSettings->email)<li><i class="bi bi-envelope me-2"></i>{{ $systemSettings->email }}</li>@endif
                                            @if ($systemSettings->adresse)<li><i class="bi bi-geo-alt me-2"></i>{{ $systemSettings->adresse }}</li>@endif
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
