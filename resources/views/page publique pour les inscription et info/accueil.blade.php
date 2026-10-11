@extends('site::layout')

@section('titre', 'Accueil')
@section('description', 'Présentation, informations utiles et démarches d’inscription de ' . $systemSettings->nom_etablissement . '.')

@section('contenu')
    @php
        $modules = [
            ['icone' => 'bi-clipboard-check', 'titre' => 'Inscriptions & admissions', 'texte' => 'Déposer une demande d’inscription en ligne, suivre son examen et recevoir la décision de l’administration.'],
            ['icone' => 'bi-person-vcard', 'titre' => 'Dossiers élèves', 'texte' => 'Identité, classe, responsable légal, documents et historique scolaire réunis dans une fiche unique.'],
            ['icone' => 'bi-journal-check', 'titre' => 'Notes & bulletins', 'texte' => 'Évaluations par période, saisie des cotes et impression des bulletins de fin de période.'],
            ['icone' => 'bi-cash-coin', 'titre' => 'Frais & paiements', 'texte' => 'Suivi des frais scolaires, encaissements, reçus et situation de recouvrement par élève.'],
            ['icone' => 'bi-calendar-week', 'titre' => 'Horaires & présences', 'texte' => 'Emplois du temps des classes et des professeurs, appel journalier et absences.'],
            ['icone' => 'bi-chat-dots', 'titre' => 'Annonces & messagerie', 'texte' => 'Communication interne entre l’administration, les titulaires, les professeurs et les parents.'],
        ];
        $acces = [
            ['route' => 'registration.student.create', 'icone' => 'bi-person-plus', 'titre' => 'Demander une inscription', 'texte' => 'Nouvel élève : introduire une demande d’admission pour une classe.'],
            ['route' => 'registration.account.create', 'icone' => 'bi-person-badge', 'titre' => 'Demander un compte', 'texte' => 'Membre du personnel : solliciter un accès à l’espace sécurisé.'],
            ['route' => 'login', 'icone' => 'bi-box-arrow-in-right', 'titre' => 'Se connecter', 'texte' => 'Vous avez déjà un compte ? Accédez à votre espace personnel.'],
        ];
    @endphp

    <!-- En-tête -->
    <header class="py-5">
        <div class="container px-4 px-lg-5 pb-5">
            <div class="row gx-5 align-items-center">
                <div class="col-xxl-6">
                    <div class="text-center text-xxl-start">
                        <div class="badge bg-gradient-primary-to-secondary text-white mb-4">
                            <div class="text-uppercase">Établissement scolaire &middot; Gestion en ligne</div>
                        </div>
                        <div class="fs-4 fw-light text-muted mb-2">{{ $systemSettings->nom_etablissement }}</div>
                        <h1 class="display-4 fw-bolder mb-4">Une scolarité suivie de près, <span class="text-gradient d-inline">de l’inscription au bulletin</span></h1>
                        <p class="lead text-muted mb-4">
                            Bienvenue sur l’espace public de notre école. Vous y trouvez les informations pratiques, les conditions
                            d’admission et le formulaire pour introduire une demande d’inscription. L’application interne, elle,
                            permet à l’administration, aux titulaires et aux professeurs de suivre les élèves, les cotes, les
                            présences et les paiements tout au long de l’année scolaire.
                        </p>
                        <div class="d-grid gap-3 d-sm-flex justify-content-sm-center justify-content-xxl-start mb-4">
                            <a class="btn btn-primary btn-lg px-4 py-3 fs-6 fw-bolder" href="{{ route('registration.student.create') }}">
                                <i class="bi bi-person-plus me-1"></i> Demander une inscription
                            </a>
                            <a class="btn btn-outline-dark btn-lg px-4 py-3 fs-6 fw-bolder" href="{{ route('site.inscriptions') }}">
                                <i class="bi bi-journal-text me-1"></i> Conditions d’admission
                            </a>
                        </div>
                        <p class="text-muted small mb-0">
                            Déjà inscrit ou membre du personnel ?
                            <a class="fw-bolder text-primary" href="{{ route('login') }}">Accéder à l’espace connexion</a>
                            <span class="text-muted">ou</span>
                            <a class="fw-bolder text-primary" href="{{ route('login.pin') }}">connexion par PIN</a>.
                        </p>
                    </div>
                </div>
                <div class="col-xxl-6">
                    <div class="card border-0 rounded-4 carte-douce mt-5 mt-xxl-0">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h2 class="h5 fw-bolder mb-0">L’espace numérique de l’école</h2>
                                <i class="bi bi-mortarboard text-primary fs-3"></i>
                            </div>
                            <p class="text-muted small">Un seul outil pour la scolarité, les évaluations, la discipline et les finances.</p>
                            <ul class="list-unstyled small mb-4">
                                @foreach (array_slice($modules, 0, 4) as $module)
                                    <li class="mb-2"><i class="bi {{ $module['icone'] }} text-primary me-2"></i>{{ $module['titre'] }}</li>
                                @endforeach
                            </ul>
                            <hr>
                            <dl class="row small mb-0">
                                <dt class="col-7 fw-normal text-muted">Année scolaire en cours</dt>
                                <dd class="col-5 text-end fw-bolder mb-2">{{ $anneeEnCours?->libelle ?? 'Non ouverte' }}</dd>
                                <dt class="col-7 fw-normal text-muted">Demandes d’inscription</dt>
                                <dd class="col-5 text-end fw-bolder mb-2">
                                    @if ($anneeEnCours && $classesActives->isNotEmpty())
                                        <span class="text-success">Ouvertes</span>
                                    @else
                                        <span class="text-danger">Fermées</span>
                                    @endif
                                </dd>
                                <dt class="col-7 fw-normal text-muted">Classes ouvertes</dt>
                                <dd class="col-5 text-end fw-bolder mb-2">{{ $classesActives->count() }}</dd>
                                @if ($systemSettings->portail_parents_actif)
                                    <dt class="col-7 fw-normal text-muted">Portail parents</dt>
                                    <dd class="col-5 text-end fw-bolder mb-0"><span class="text-success">Actif</span></dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Démarches rapides -->
    <section class="py-4 bg-light">
        <div class="container px-4 px-lg-5 py-4">
            <div class="row gx-4 gy-4">
                @foreach ($acces as $lien)
                    <div class="col-md-4">
                        <a class="text-decoration-none" href="{{ route($lien['route']) }}">
                            <div class="card h-100 border-0 rounded-4 carte-douce">
                                <div class="card-body p-4">
                                    <i class="bi {{ $lien['icone'] }} fs-2 text-primary"></i>
                                    <h3 class="h6 fw-bolder mt-3 mb-1 text-dark">{{ $lien['titre'] }}</h3>
                                    <p class="small text-muted mb-0">{{ $lien['texte'] }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Ce que l'application permet -->
    <section class="py-5">
        <div class="container px-4 px-lg-5 py-5">
            <div class="text-center mb-5">
                <div class="badge bg-gradient-primary-to-secondary text-white mb-3"><div class="text-uppercase">Fonctionnalités</div></div>
                <h2 class="section-titre display-6 mb-2">L’application en un coup d’œil</h2>
                <p class="lead text-muted mb-0">Les modules utilisés quotidiennement par l’administration et le personnel enseignant.</p>
            </div>
            <div class="row gx-4 gy-4">
                @foreach ($modules as $module)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 border-0 rounded-4 carte-douce">
                            <div class="card-body p-4">
                                <i class="bi {{ $module['icone'] }} fs-2 text-primary"></i>
                                <h3 class="h6 fw-bolder mt-3">{{ $module['titre'] }}</h3>
                                <p class="small text-muted mb-0">{{ $module['texte'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Chiffres -->
    <section class="py-5 bg-gradient-primary-to-secondary text-white">
        <div class="container px-4 px-lg-5 py-4">
            <div class="row gx-4 gy-4 text-center">
                <div class="col-md-3 col-6">
                    <div class="display-6 fw-bolder">{{ $cycles->count() }}</div>
                    <div class="small text-uppercase">Cycles d’enseignement</div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="display-6 fw-bolder">{{ $classesActives->count() }}</div>
                    <div class="small text-uppercase">Classes ouvertes</div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="display-6 fw-bolder">{{ $effectifInscrit ?? '—' }}</div>
                    <div class="small text-uppercase">Élèves inscrits</div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="display-6 fw-bolder">{{ $anneeEnCours?->libelle ?? '—' }}</div>
                    <div class="small text-uppercase">Année scolaire</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Étapes d'inscription -->
    <section class="py-5">
        <div class="container px-4 px-lg-5 py-5">
            <div class="row gx-5 align-items-start">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h2 class="section-titre display-6 mb-3">Comment s’inscrire&nbsp;?</h2>
                    <p class="text-muted">
                        La demande en ligne est une <span class="fw-bolder">demande d’admission</span> : elle est examinée par
                        l’administration avant la création de l’inscription définitive. Aucun frais n’est encaissé sur ce site.
                    </p>
                    <a class="btn btn-primary px-4 rounded-pill fw-bolder" href="{{ route('registration.student.create') }}">Introduire une demande</a>
                </div>
                <div class="col-lg-8">
                    @php
                        $etapes = [
                            ['titre' => 'Rassembler les informations', 'texte' => 'Identité de l’élève, classe souhaitée, coordonnées du parent ou du responsable légal.'],
                            ['titre' => 'Introduire la demande en ligne', 'texte' => 'Le formulaire public est disponible en permanence ; la demande est enregistrée dès l’envoi.'],
                            ['titre' => 'Examen par l’administration', 'texte' => 'L’école vérifie les places disponibles et l’aptitude de l’élève pour la classe demandée.'],
                            ['titre' => 'Inscription définitive', 'texte' => 'Après acceptation, le dossier est créé et les frais scolaires applicables vous sont communiqués.'],
                        ];
                    @endphp
                    <div class="row gx-4 gy-4">
                        @foreach ($etapes as $index => $etape)
                            <div class="col-sm-6">
                                <div class="d-flex">
                                    <span class="step-numero flex-shrink-0">{{ $index + 1 }}</span>
                                    <div class="ms-3">
                                        <h3 class="h6 fw-bolder mb-1">{{ $etape['titre'] }}</h3>
                                        <p class="small text-muted mb-0">{{ $etape['texte'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Organisation pédagogique -->
    @if ($cycles->isNotEmpty())
        <section class="py-5 bg-light">
            <div class="container px-4 px-lg-5 py-5">
                <div class="text-center mb-5">
                    <h2 class="section-titre display-6 mb-2">Notre organisation pédagogique</h2>
                    <p class="lead text-muted mb-0">Les cycles et les classes actuellement ouverts aux inscriptions.</p>
                </div>
                <div class="row gx-4 gy-4">
                    @foreach ($cycles as $cycle)
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-0 rounded-4 carte-douce">
                                <div class="card-body p-4">
                                    <h3 class="h6 fw-bolder mb-3">{{ $cycle->nom }}</h3>
                                    @forelse ($cycle->classes as $classe)
                                        <span class="badge bg-light text-dark border me-1 mb-1">{{ $classe->nom }}</span>
                                    @empty
                                        <p class="small text-muted mb-0">Aucune classe ouverte dans ce cycle pour le moment.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-4">
                    <a class="btn btn-outline-dark px-4 rounded-pill fw-bolder" href="{{ route('site.inscriptions') }}">Voir le détail des classes et des conditions</a>
                </div>
            </div>
        </section>
    @endif

    <!-- Appel à l'action -->
    <section class="py-5">
        <div class="container px-4 px-lg-5 py-5">
            <div class="card border-0 rounded-4 carte-douce">
                <div class="card-body p-4 p-lg-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                    <div class="mb-3 mb-lg-0">
                        <h2 class="h4 fw-bolder mb-1">Une question sur l’école ou sur une inscription&nbsp;?</h2>
                        <p class="text-muted mb-0">Écrivez-nous : l’administration répond aux messages reçus via le formulaire de contact.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-primary px-4 rounded-pill fw-bolder" href="{{ route('site.contact') }}">Nous contacter</a>
                        <a class="btn btn-outline-secondary px-4 rounded-pill fw-bolder" href="{{ route('site.apropos') }}">À propos de l’école</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
