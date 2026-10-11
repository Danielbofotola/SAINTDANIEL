@extends('site::layout')

@section('titre', 'Demande de compte utilisateur')
@section('description', 'Demande d’accès à l’espace sécurisé de ' . $systemSettings->nom_etablissement . ' pour le personnel et les parents.')

@section('contenu')
    <header class="py-5 bg-light">
        <div class="container px-4 px-lg-5 py-4">
            <nav aria-label="Fil d’Ariane" class="small mb-3">
                <a class="text-decoration-none" href="{{ route('site.accueil') }}">Accueil</a>
                <span class="text-muted mx-1">/</span>
                <span class="text-muted">Demande de compte</span>
            </nav>
            <div class="badge bg-gradient-primary-to-secondary text-white mb-3"><div class="text-uppercase">Accès à la plateforme</div></div>
            <h1 class="display-6 fw-bolder mb-2">Demande de compte</h1>
            <p class="lead text-muted mb-0" style="max-width: 60rem;">
                Réservée aux membres du personnel et aux parents. Votre compte sera créé et activé uniquement
                après validation par l’administration, qui confirme le rôle attribué.
            </p>
        </div>
    </header>

    <section class="py-5">
        <div class="container px-4 px-lg-5 py-4">
            <div class="row gx-5 gy-4">
                <div class="col-lg-8">
                    @if (session('success'))
                        <div class="alert alert-success rounded-4" role="status"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-4" role="alert">
                            <div class="fw-bolder mb-1">La demande n’a pas pu être transmise :</div>
                            <ul class="mb-0 small">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <div class="card border-0 rounded-4 form-panel">
                        <div class="card-body p-4 p-lg-5">
                            <form method="POST" action="{{ route('registration.account.store') }}">
                                @csrf

                                <h2 class="section-titre-form">Identité</h2>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="nom">Nom *</label>
                                        <input class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom') }}" maxlength="50" required>
                                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="postnom">Post-nom</label>
                                        <input class="form-control @error('postnom') is-invalid @enderror" id="postnom" name="postnom" value="{{ old('postnom') }}" maxlength="50">
                                        @error('postnom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="prenom">Prénom *</label>
                                        <input class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" value="{{ old('prenom') }}" maxlength="50" required>
                                        @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="telephone">Téléphone</label>
                                        <input class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone') }}" maxlength="20">
                                        @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="email">E-mail *</label>
                                        <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" maxlength="100" autocomplete="email" required>
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <h2 class="section-titre-form mt-4">Accès demandé</h2>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="username">Nom d’utilisateur souhaité *</label>
                                        <input class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" maxlength="50" autocomplete="username" required>
                                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="role_souhaite">Rôle demandé *</label>
                                        <select class="form-select @error('role_souhaite') is-invalid @enderror" id="role_souhaite" name="role_souhaite" required>
                                            <option value="">Sélectionner un rôle</option>
                                            <option value="prefetture" @selected(old('role_souhaite') === 'prefetture')>Préfecture</option>
                                            <option value="titulaire" @selected(old('role_souhaite') === 'titulaire')>Titulaire</option>
                                            <option value="professeur" @selected(old('role_souhaite') === 'professeur')>Professeur</option>
                                            <option value="comptable" @selected(old('role_souhaite') === 'comptable')>Comptable</option>
                                            <option value="parent" @selected(old('role_souhaite') === 'parent')>Parent</option>
                                        </select>
                                        @error('role_souhaite')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="form-text">L’administration confirmera le rôle avant l’activation du compte.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="mot_de_passe">Mot de passe *</label>
                                        <input class="form-control @error('mot_de_passe') is-invalid @enderror" id="mot_de_passe" type="password" name="mot_de_passe" minlength="8" autocomplete="new-password" required>
                                        @error('mot_de_passe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="form-text">8 caractères au minimum.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="mot_de_passe_confirmation">Confirmer le mot de passe *</label>
                                        <input class="form-control" id="mot_de_passe_confirmation" type="password" name="mot_de_passe_confirmation" minlength="8" autocomplete="new-password" required>
                                    </div>
                                </div>

                                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                                    <a class="btn btn-outline-secondary px-4 rounded-pill fw-bolder" href="{{ route('login') }}">Annuler</a>
                                    <button class="btn btn-primary px-4 rounded-pill fw-bolder" type="submit">
                                        <i class="bi bi-send me-1"></i> Envoyer la demande
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 rounded-4 bg-light mb-4">
                        <div class="card-body p-4">
                            <h2 class="h6 fw-bolder mb-3"><i class="bi bi-person-badge text-primary me-2"></i>Quel rôle demander&nbsp;?</h2>
                            <ul class="list-unstyled small text-muted mb-0">
                                <li class="mb-2"><span class="fw-bolder text-dark">Préfecture</span> — supervision pédagogique : évaluations, cotes, délibérations.</li>
                                <li class="mb-2"><span class="fw-bolder text-dark">Titulaire</span> — suivi d’une classe : bulletins, appel, relation avec les parents.</li>
                                <li class="mb-2"><span class="fw-bolder text-dark">Professeur</span> — cours affectés, cahier de cotes et présences.</li>
                                <li class="mb-2"><span class="fw-bolder text-dark">Comptable</span> — paiements, frais, dépenses et caisse.</li>
                                <li><span class="fw-bolder text-dark">Parent</span> — consultation des notes et des présences de son enfant.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="card border-0 rounded-4 carte-douce">
                        <div class="card-body p-4">
                            <h2 class="h6 fw-bolder mb-2">Déjà un compte&nbsp;?</h2>
                            <p class="small text-muted">Connectez-vous avec votre identifiant ou par code PIN.</p>
                            <div class="d-grid gap-2">
                                <a class="btn btn-primary rounded-pill fw-bolder" href="{{ route('login') }}">Espace connexion</a>
                                <a class="btn btn-outline-secondary rounded-pill fw-bolder" href="{{ route('login.pin') }}">Connexion par PIN</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
