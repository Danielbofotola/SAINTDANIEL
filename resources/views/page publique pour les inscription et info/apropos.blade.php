@extends('site::layout')

@section('titre', 'À propos')
@section('description', 'Présentation, mission, organisation pédagogique et espace numérique de ' . $systemSettings->nom_etablissement . '.')

@section('contenu')
    @php
        $valeurs = [
            ['icone' => 'bi-award', 'titre' => 'Rigueur académique', 'texte' => 'Des évaluations régulières par période et des bulletins établis sur base des cotes réellement saisies.'],
            ['icone' => 'bi-people', 'titre' => 'Accompagnement', 'texte' => 'Des titulaires de classe chargés du suivi individuel et de la relation avec les familles.'],
            ['icone' => 'bi-clipboard-data', 'titre' => 'Transparence', 'texte' => 'Une situation financière par élève : frais attendus, encaissements et reçus consultables.'],
            ['icone' => 'bi-shield-check', 'titre' => 'Discipline & sécurité', 'texte' => 'Appel journalier, suivi des absences, sanctions et notes de conduite consignées.'],
        ];
        $roles = [
            ['icone' => 'bi-building', 'titre' => 'Administration', 'texte' => 'Direction, secrétariat et gestion : admissions, classes, frais, personnel et archives annuelles.'],
            ['icone' => 'bi-eye', 'titre' => 'Préfecture des études', 'texte' => 'Supervision pédagogique : évaluations, saisie des cotes, délibérations et vie scolaire.'],
            ['icone' => 'bi-person-lines-fill', 'titre' => 'Titulaires de classe', 'texte' => 'Suivi quotidien de la classe, bulletins, appel et communication avec les parents.'],
            ['icone' => 'bi-journal-bookmark', 'titre' => 'Professeurs', 'texte' => 'Cours affectés, cahier de cotes, présences et commentaires de fin de période.'],
            ['icone' => 'bi-calculator', 'titre' => 'Comptabilité', 'texte' => 'Paiements, dépenses, caisse, recouvrement et écritures comptables.'],
            ['icone' => 'bi-house-heart', 'titre' => 'Parents & élèves', 'texte' => 'Consultation des notes, des présences et des annonces via le portail parents.'],
        ];
    @endphp

    <header class="py-5 bg-light">
        <div class="container px-4 px-lg-5 py-4">
            <div class="badge bg-gradient-primary-to-secondary text-white mb-3"><div class="text-uppercase">Notre établissement</div></div>
            <h1 class="display-5 fw-bolder mb-3">À propos de <span class="text-gradient d-inline">{{ $systemSettings->nom_etablissement }}</span></h1>
            <p class="lead text-muted mb-0" style="max-width: 60rem;">
                {{ $systemSettings->nom_etablissement }} — également connu sous le sigle {{ $systemSettings->nom_court }} —
                est un ensemble scolaire qui accueille les élèves de plusieurs cycles d’enseignement et s’appuie sur une
                organisation numérique pour suivre la scolarité de chaque enfant, de l’admission au bulletin annuel.
            </p>
        </div>
    </header>

    <section class="py-5">
        <div class="container px-4 px-lg-5 py-5">
            <div class="row gx-5 gy-4">
                <div class="col-lg-6">
                    <h2 class="section-titre h4 mb-3"><i class="bi bi-bullseye text-primary me-2"></i>Notre mission</h2>
                    <p class="text-muted">
                        Offrir un enseignement exigeant et un suivi personnalisé à chaque élève, en garantissant aux familles
                        une information claire sur les résultats, les présences et la situation scolaire de leur enfant.
                    </p>
                </div>
                <div class="col-lg-6">
                    <h2 class="section-titre h4 mb-3"><i class="bi bi-binoculars text-primary me-2"></i>Notre ambition</h2>
                    <p class="text-muted">
                        Faire de la donnée scolaire un outil de progrès : des décisions d’orientation fondées sur les résultats
                        réels, des délibérations équitables et une administration qui répond rapidement aux demandes des parents.
                    </p>
                </div>
            </div>

            <div class="row gx-4 gy-4 mt-2">
                @foreach ($valeurs as $valeur)
                    <div class="col-md-6 col-xl-3">
                        <div class="card h-100 border-0 rounded-4 carte-douce">
                            <div class="card-body p-4">
                                <i class="bi {{ $valeur['icone'] }} fs-3 text-primary"></i>
                                <h3 class="h6 fw-bolder mt-3">{{ $valeur['titre'] }}</h3>
                                <p class="small text-muted mb-0">{{ $valeur['texte'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($cycles->isNotEmpty())
        <section class="py-5 bg-light">
            <div class="container px-4 px-lg-5 py-5">
                <h2 class="section-titre display-6 mb-2">Organisation pédagogique</h2>
                <p class="text-muted mb-4">Les cycles d’enseignement et le nombre de classes actuellement ouvertes.</p>
                <div class="row gx-4 gy-4">
                    @foreach ($cycles as $cycle)
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-0 rounded-4 carte-douce">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h3 class="h6 fw-bolder mb-0">{{ $cycle->nom }}</h3>
                                        <span class="badge bg-primary rounded-pill">{{ $cycle->classes->count() }} classe(s)</span>
                                    </div>
                                    @forelse ($cycle->classes as $classe)
                                        <span class="badge bg-light text-dark border me-1 mb-1">{{ $classe->nom }}</span>
                                    @empty
                                        <p class="small text-muted mb-0">Aucune classe ouverte dans ce cycle.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if ($effectifInscrit !== null)
                    <p class="text-muted small mt-4 mb-0">
                        Effectif actuellement inscrit{{ $anneeEnCours ? ' en ' . $anneeEnCours->libelle : '' }} :
                        <span class="fw-bolder">{{ $effectifInscrit }}</span> élève(s), répartis dans {{ $classesActives->count() }} classe(s).
                    </p>
                @endif
            </div>
        </section>
    @endif

    <section class="py-5">
        <div class="container px-4 px-lg-5 py-5">
            <h2 class="section-titre display-6 mb-2">Qui fait quoi&nbsp;?</h2>
            <p class="text-muted mb-4">L’application attribue à chaque membre de la communauté scolaire un espace adapté à son rôle.</p>
            <div class="row gx-4 gy-4">
                @foreach ($roles as $role)
                    <div class="col-md-6 col-xl-4">
                        <div class="d-flex">
                            <i class="bi {{ $role['icone'] }} fs-4 text-primary me-3"></i>
                            <div>
                                <h3 class="h6 fw-bolder mb-1">{{ $role['titre'] }}</h3>
                                <p class="small text-muted mb-0">{{ $role['texte'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container px-4 px-lg-5 py-5">
            <div class="row gx-5 gy-4 align-items-center">
                <div class="col-lg-7">
                    <h2 class="section-titre h3 mb-3">L’application au service de l’école</h2>
                    <p class="text-muted">
                        L’espace numérique couvre l’ensemble du parcours scolaire : admissions, dossiers élèves, emplois du temps,
                        cotes et bulletins, présences, paiements et frais, dépenses et comptabilité, ainsi que la communication
                        interne et les annonces.
                    </p>
                    <ul class="list-unstyled text-muted mb-0">
                        <li class="d-flex mb-2"><i class="bi bi-lock text-primary me-2 mt-1"></i><span>Chaque utilisateur ne voit que les données correspondant à son rôle : l’accès est nominatif et vérifié à la connexion.</span></li>
                        <li class="d-flex mb-2"><i class="bi bi-clock-history text-primary me-2 mt-1"></i><span>Les données sont rattachées à l’année scolaire : la clôture d’une année conserve un historique consultable.</span></li>
                        <li class="d-flex mb-2"><i class="bi bi-archive text-primary me-2 mt-1"></i><span>Les documents et pièces justificatives sont rattachés au dossier de l’élève ou du membre du personnel.</span></li>
                    </ul>
                </div>
                <div class="col-lg-5">
                    <div class="card border-0 rounded-4 bg-gradient-primary-to-secondary text-white">
                        <div class="card-body p-4 p-lg-5">
                            <h3 class="h5 fw-bolder mb-2">Rejoindre l’établissement</h3>
                            <p class="opacity-75">Consultez les conditions d’admission ou introduisez directement une demande d’inscription.</p>
                            <div class="d-grid gap-2">
                                <a class="btn btn-light fw-bolder rounded-pill" href="{{ route('site.inscriptions') }}">Conditions d’admission</a>
                                <a class="btn btn-outline-light fw-bolder rounded-pill" href="{{ route('site.contact') }}">Nous contacter</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
