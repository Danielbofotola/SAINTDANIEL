@extends('site::layout')

@section('titre', 'Contact')
@section('description', 'Coordonnées et formulaire de contact de ' . $systemSettings->nom_etablissement . ' : renseignements, inscriptions, frais scolaires.')

@section('contenu')
    @php
        $coordonnees = array_filter([
            ['icone' => 'bi-telephone', 'libelle' => 'Téléphone', 'valeur' => $systemSettings->telephone, 'lien' => $systemSettings->telephone ? 'tel:' . preg_replace('/\s+/', '', $systemSettings->telephone) : null],
            ['icone' => 'bi-envelope', 'libelle' => 'E-mail', 'valeur' => $systemSettings->email, 'lien' => $systemSettings->email ? 'mailto:' . $systemSettings->email : null],
            ['icone' => 'bi-geo-alt', 'libelle' => 'Adresse', 'valeur' => $systemSettings->adresse, 'lien' => null],
        ], fn ($ligne) => filled($ligne['valeur']));
    @endphp

    <header class="py-5 bg-light">
        <div class="container px-4 px-lg-5 py-4">
            <div class="badge bg-gradient-primary-to-secondary text-white mb-3"><div class="text-uppercase">Nous joindre</div></div>
            <h1 class="display-5 fw-bolder mb-3">Contact &amp; <span class="text-gradient d-inline">renseignements</span></h1>
            <p class="lead text-muted mb-0" style="max-width: 60rem;">
                Une question sur une inscription, les frais scolaires, un dossier d’élève ou un accès à votre compte&nbsp;?
                Écrivez-nous : chaque message est enregistré et traité par l’administration de {{ $systemSettings->nom_etablissement }}.
            </p>
        </div>
    </header>

    <section class="py-5">
        <div class="container px-4 px-lg-5 py-4">
            <div class="row gx-4 gy-4 mb-2">
                @forelse ($coordonnees as $coordonnee)
                    <div class="col-md-4">
                        <div class="card h-100 border-0 rounded-4 carte-douce">
                            <div class="card-body p-4">
                                <i class="bi {{ $coordonnee['icone'] }} fs-3 text-primary"></i>
                                <div class="small text-uppercase text-muted fw-bolder mt-3">{{ $coordonnee['libelle'] }}</div>
                                @if ($coordonnee['lien'])
                                    <a class="fw-bolder text-decoration-none text-dark" href="{{ $coordonnee['lien'] }}">{{ $coordonnee['valeur'] }}</a>
                                @else
                                    <div class="fw-bolder text-dark">{{ $coordonnee['valeur'] }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-light border rounded-4 mb-0">
                            Les coordonnées de l’école sont en cours de mise à jour. Utilisez le formulaire ci-dessous pour nous joindre.
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="row gx-5 gy-5 mt-2">
                <div class="col-lg-7">
                    <div class="card border-0 rounded-4 carte-douce">
                        <div class="card-body p-4 p-lg-5">
                            <h2 class="section-titre h4 mb-1">Écrire à l’administration</h2>
                            <p class="text-muted small mb-4">Les champs marqués d’un astérisque (*) sont obligatoires.</p>

                            @if (session('success'))
                                <div class="alert alert-success rounded-4" role="status"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger rounded-4" role="alert">{{ session('error') }}</div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger rounded-4" role="alert">
                                    <div class="fw-bolder mb-1">Votre message n’a pas pu être transmis :</div>
                                    <ul class="mb-0 small">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('site.contact.store') }}" novalidate>
                                @csrf
                                <div class="visually-hidden" aria-hidden="true">
                                    <label for="site_web">Ne pas remplir ce champ</label>
                                    <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bolder" for="nom">Nom et prénom *</label>
                                        <input class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom') }}" maxlength="100" required>
                                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bolder" for="email">Adresse e-mail *</label>
                                        <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" maxlength="100" required>
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bolder" for="telephone">Téléphone</label>
                                        <input class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone') }}" maxlength="20" placeholder="Ex. 0812345678">
                                        @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bolder" for="sujet">Motif du message *</label>
                                        <select class="form-select @error('sujet') is-invalid @enderror" id="sujet" name="sujet" required>
                                            <option value="">Choisir un motif</option>
                                            @foreach ($sujets as $sujet)
                                                <option value="{{ $sujet }}" @selected(old('sujet') === $sujet)>{{ $sujet }}</option>
                                            @endforeach
                                        </select>
                                        @error('sujet')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label small fw-bolder" for="message">Votre message *</label>
                                        <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="6" maxlength="3000"
                                                  placeholder="Décrivez votre demande : nom de l’élève concerné, classe, année scolaire…" required>{{ old('message') }}</textarea>
                                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="form-text">3000 caractères au maximum.</div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                    <p class="small text-muted mb-0">
                                        Vos coordonnées ne servent qu’à traiter votre demande.
                                    </p>
                                    <button class="btn btn-primary px-4 rounded-pill fw-bolder" type="submit">
                                        <i class="bi bi-send me-1"></i> Envoyer le message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 rounded-4 bg-light mb-4">
                        <div class="card-body p-4">
                            <h3 class="h6 fw-bolder mb-3"><i class="bi bi-clock text-primary me-2"></i>Heures d’ouverture du secrétariat</h3>
                            <ul class="list-unstyled small text-muted mb-0">
                                <li class="d-flex justify-content-between mb-1"><span>Lundi – vendredi</span><span class="fw-bolder">07h30 – 16h30</span></li>
                                <li class="d-flex justify-content-between mb-1"><span>Samedi</span><span class="fw-bolder">08h00 – 12h00</span></li>
                                <li class="d-flex justify-content-between"><span>Dimanche &amp; jours fériés</span><span class="fw-bolder">Fermé</span></li>
                            </ul>
                            <p class="small text-muted mt-3 mb-0">Horaires indicatifs — à ajuster par l’administration selon le calendrier de l’établissement.</p>
                        </div>
                    </div>

                    <div class="card border-0 rounded-4 carte-douce">
                        <div class="card-body p-4">
                            <h3 class="h6 fw-bolder mb-3">Démarches en ligne</h3>
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2"><a class="text-decoration-none fw-bolder" href="{{ route('registration.student.create') }}"><i class="bi bi-person-plus text-primary me-2"></i>Demander une inscription scolaire</a></li>
                                <li class="mb-2"><a class="text-decoration-none fw-bolder" href="{{ route('registration.account.create') }}"><i class="bi bi-person-badge text-primary me-2"></i>Demander un compte utilisateur</a></li>
                                <li class="mb-2"><a class="text-decoration-none fw-bolder" href="{{ route('site.inscriptions') }}"><i class="bi bi-journal-text text-primary me-2"></i>Conditions d’admission</a></li>
                                <li><a class="text-decoration-none fw-bolder" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right text-primary me-2"></i>Espace connexion</a></li>
                            </ul>
                            <p class="small text-muted mt-3 mb-0">
                                Délai de réponse : sous 72 heures ouvrables. Pour une urgence, privilégiez le téléphone ou le passage au secrétariat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
