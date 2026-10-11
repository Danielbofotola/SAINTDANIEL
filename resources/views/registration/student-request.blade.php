@extends('site::layout')

@section('titre', 'Demande d’inscription scolaire')
@section('description', 'Formulaire de demande d’admission pour un nouvel élève à ' . $systemSettings->nom_etablissement . '.')

@section('contenu')
    @php($demandePossible = $classes->isNotEmpty() && $anneeEnCours !== null)

    <header class="py-5 bg-light">
        <div class="container px-4 px-lg-5 py-4">
            <nav aria-label="Fil d’Ariane" class="small mb-3">
                <a class="text-decoration-none" href="{{ route('site.accueil') }}">Accueil</a>
                <span class="text-muted mx-1">/</span>
                <a class="text-decoration-none" href="{{ route('site.inscriptions') }}">Inscriptions</a>
                <span class="text-muted mx-1">/</span>
                <span class="text-muted">Demande d’inscription</span>
            </nav>
            <div class="badge bg-gradient-primary-to-secondary text-white mb-3"><div class="text-uppercase">Admission scolaire</div></div>
            <h1 class="display-6 fw-bolder mb-2">Demande d’inscription</h1>
            <p class="lead text-muted mb-0" style="max-width: 60rem;">
                Cette demande est un <span class="fw-bolder">dossier d’admission</span> : elle sera examinée par
                l’administration avant la création de l’inscription définitive. Aucun paiement n’est demandé en ligne.
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
                    @unless ($demandePossible)
                        <div class="alert alert-warning rounded-4" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Les demandes ne peuvent pas être transmises pour le moment : l’administration doit d’abord
                            ouvrir une année scolaire et au moins une classe.
                        </div>
                    @endunless

                    <div class="card border-0 rounded-4 form-panel">
                        <div class="card-body p-4 p-lg-5">
                            <form method="POST" action="{{ route('registration.student.store') }}">
                                @csrf

                                <h2 class="section-titre-form">Élève</h2>
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
                                    <div class="col-md-4">
                                        <label class="form-label" for="sexe">Sexe *</label>
                                        <select class="form-select @error('sexe') is-invalid @enderror" id="sexe" name="sexe" required>
                                            <option value="">Choisir</option>
                                            <option value="M" @selected(old('sexe') === 'M')>Garçon</option>
                                            <option value="F" @selected(old('sexe') === 'F')>Fille</option>
                                        </select>
                                        @error('sexe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="date_naissance">Date de naissance *</label>
                                        <input class="form-control @error('date_naissance') is-invalid @enderror" id="date_naissance" type="text" data-date-fr name="date_naissance" value="{{ old('date_naissance') }}" required>
                                        @error('date_naissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="lieu_naissance">Lieu de naissance</label>
                                        <input class="form-control @error('lieu_naissance') is-invalid @enderror" id="lieu_naissance" name="lieu_naissance" value="{{ old('lieu_naissance') }}" maxlength="100">
                                        @error('lieu_naissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <h2 class="section-titre-form mt-4">Inscription souhaitée</h2>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="classe_id">Classe demandée *</label>
                                        <select class="form-select @error('classe_id') is-invalid @enderror" id="classe_id" name="classe_id" required>
                                            <option value="">Sélectionner une classe</option>
                                            @foreach ($classes as $classe)
                                                <option value="{{ $classe->id }}" @selected(old('classe_id') == $classe->id)>{{ $classe->nom }} · {{ $classe->cycle?->nom }}</option>
                                            @endforeach
                                        </select>
                                        @error('classe_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="annee_scolaire_id">Année scolaire *</label>
                                        @if ($anneeEnCours)
                                            <input class="form-control" id="annee_scolaire_id" value="{{ $anneeEnCours->libelle }}" disabled>
                                            <div class="form-text">L’année en cours est appliquée automatiquement à la demande.</div>
                                        @else
                                            <div class="alert alert-warning py-2 mb-0">Aucune année scolaire n’est ouverte pour le moment.</div>
                                        @endif
                                    </div>
                                </div>

                                <h2 class="section-titre-form mt-4">Parent ou responsable légal</h2>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="nom_mere">Nom du parent / responsable *</label>
                                        <input class="form-control @error('nom_mere') is-invalid @enderror" id="nom_mere" name="nom_mere" value="{{ old('nom_mere') }}" maxlength="100" required>
                                        @error('nom_mere')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="telephone_mere">Téléphone *</label>
                                        <input class="form-control @error('telephone_mere') is-invalid @enderror" id="telephone_mere" name="telephone_mere" value="{{ old('telephone_mere') }}" maxlength="20" required>
                                        @error('telephone_mere')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="email_mere">E-mail</label>
                                        <input class="form-control @error('email_mere') is-invalid @enderror" id="email_mere" type="email" name="email_mere" value="{{ old('email_mere') }}" maxlength="100">
                                        @error('email_mere')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="telephone_pere">Autre téléphone</label>
                                        <input class="form-control @error('telephone_pere') is-invalid @enderror" id="telephone_pere" name="telephone_pere" value="{{ old('telephone_pere') }}" maxlength="20">
                                        @error('telephone_pere')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="adresse_mere">Adresse</label>
                                        <textarea class="form-control @error('adresse_mere') is-invalid @enderror" id="adresse_mere" name="adresse_mere" rows="2" maxlength="1000">{{ old('adresse_mere') }}</textarea>
                                        @error('adresse_mere')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                                    <a class="btn btn-outline-secondary px-4 rounded-pill fw-bolder" href="{{ route('site.inscriptions') }}">Annuler</a>
                                    <button class="btn btn-primary px-4 rounded-pill fw-bolder" type="submit" @disabled(! $demandePossible)>
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
                            <h2 class="h6 fw-bolder mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Avant de commencer</h2>
                            <ul class="list-unstyled small text-muted mb-0">
                                <li class="d-flex mb-2"><i class="bi bi-check2 text-primary me-2 mt-1"></i><span>Renseignez l’identité de l’élève telle qu’elle figure sur l’acte de naissance.</span></li>
                                <li class="d-flex mb-2"><i class="bi bi-check2 text-primary me-2 mt-1"></i><span>Choisissez la classe correspondant au niveau réel de l’élève.</span></li>
                                <li class="d-flex mb-2"><i class="bi bi-check2 text-primary me-2 mt-1"></i><span>Indiquez un numéro de téléphone joignable : c’est par là que l’école vous répond.</span></li>
                                <li class="d-flex"><i class="bi bi-check2 text-primary me-2 mt-1"></i><span>Présentez ensuite les pièces du dossier au secrétariat.</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card border-0 rounded-4 carte-douce">
                        <div class="card-body p-4">
                            <h2 class="h6 fw-bolder mb-2">Une hésitation&nbsp;?</h2>
                            <p class="small text-muted">Consultez les conditions d’admission ou écrivez à l’administration avant de transmettre le dossier.</p>
                            <div class="d-grid gap-2">
                                <a class="btn btn-outline-primary rounded-pill fw-bolder" href="{{ route('site.inscriptions') }}">Conditions d’admission</a>
                                <a class="btn btn-outline-secondary rounded-pill fw-bolder" href="{{ route('site.contact') }}">Contacter l’école</a>
                                @if ($systemSettings->telephone)
                                    <p class="small text-muted text-center mb-0 mt-2"><i class="bi bi-telephone me-1"></i>{{ $systemSettings->telephone }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
