@extends('site::layout')

@section('titre', 'Inscriptions')
@section('description', 'Conditions d’admission, documents à fournir, classes ouvertes et démarches d’inscription de ' . $systemSettings->nom_etablissement . '.')

@section('contenu')
    @php
        $demandesOuvertes = $anneeEnCours !== null && $classesActives->isNotEmpty();
        $conditions = [
            'Présenter un âge compatible avec la classe demandée, selon la réglementation scolaire en vigueur.',
            'Produire les documents originaux listés ci-dessous : aucune inscription définitive n’est créée sans dossier complet.',
            'Fournir les coordonnées d’un parent ou d’un responsable légal joignable en cas de besoin.',
            'Se présenter à l’entretien ou au test d’orientation si l’école le demande après examen de la demande.',
            'S’acquitter des frais scolaires applicables à la classe, au bureau de l’école, sur reçu.',
        ];
        $pieces = [
            'Bulletin ou attestation de réussite de l’année précédente',
            'Copie de l’acte de naissance ou de l’attestation d’âge',
            'Deux photos passeport récentes',
            'Fiche de renseignements du parent ou du responsable légal (nom, téléphone, adresse)',
            'Attestation de transfert pour les élèves venant d’un autre établissement',
        ];
        $etapes = [
            ['titre' => 'Vérifier la classe souhaitée', 'texte' => 'Consultez la liste des classes ouvertes ci-dessous et le cycle correspondant.'],
            ['titre' => 'Compléter le formulaire en ligne', 'texte' => 'Renseignez l’identité de l’élève, la classe demandée et les coordonnées du responsable légal.'],
            ['titre' => 'Attendre la décision de l’administration', 'texte' => 'Chaque demande est examinée : vous êtes contacté au numéro ou à l’adresse e-mail indiqués.'],
            ['titre' => 'Finaliser l’inscription', 'texte' => 'En cas d’acceptation, présentez les pièces demandées et réglez les frais au bureau de l’école.'],
        ];
        $faq = [
            ['question' => 'La demande en ligne suffit-elle pour être inscrit ?', 'reponse' => 'Non. Elle constitue une demande d’admission examinée par l’administration. L’inscription n’est définitive qu’après acceptation et présentation du dossier complet.'],
            ['question' => 'Peut-on payer les frais scolaires en ligne ?', 'reponse' => 'Le paiement des frais se fait au bureau de l’école, contre reçu. L’application ne réalise aucune transaction financière depuis les pages publiques.'],
            ['question' => 'Combien de temps prend l’examen d’une demande ?', 'reponse' => 'Le délai dépend de la période : il est plus court en début d’année scolaire. Vous pouvez relancer l’administration via le formulaire de contact en indiquant vos coordonnées.'],
            ['question' => 'Un élève venant d’une autre école peut-il être admis ?', 'reponse' => 'Oui, sous réserve de présenter l’attestation de transfert et le bulletin de son ancienne école, et qu’une place soit disponible dans la classe demandée.'],
            ['question' => 'Que faire si aucune classe ou aucune année n’est ouverte ?', 'reponse' => 'Les demandes sont alors suspendues : l’administration doit d’abord ouvrir l’année scolaire et les classes concernées. Contactez l’école pour connaître la date d’ouverture.'],
        ];
    @endphp

    <header class="py-5 bg-light">
        <div class="container px-4 px-lg-5 py-4">
            <div class="badge bg-gradient-primary-to-secondary text-white mb-3"><div class="text-uppercase">Admissions</div></div>
            <h1 class="display-5 fw-bolder mb-3">Inscriptions <span class="text-gradient d-inline">{{ $anneeEnCours?->libelle ?? 'prochaine rentrée' }}</span></h1>
            <p class="lead text-muted mb-4" style="max-width: 60rem;">
                Toutes les informations utiles pour inscrire un élève à {{ $systemSettings->nom_etablissement }} :
                conditions d’admission, documents à fournir, classes ouvertes et démarches en ligne.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-primary px-4 py-3 fw-bolder" href="{{ route('registration.student.create') }}"><i class="bi bi-person-plus me-1"></i> Demander une inscription scolaire</a>
                <a class="btn btn-outline-dark px-4 py-3 fw-bolder" href="{{ route('registration.account.create') }}"><i class="bi bi-person-badge me-1"></i> Demander un compte utilisateur</a>
            </div>
        </div>
    </header>

    <section class="py-4">
        <div class="container px-4 px-lg-5">
            @if ($demandesOuvertes)
                <div class="alert alert-success rounded-4 border-0 shadow-sm mb-0" role="status">
                    <i class="bi bi-check-circle me-1"></i>
                    <span class="fw-bolder">Les demandes d’inscription sont ouvertes</span> pour l’année scolaire
                    {{ $anneeEnCours->libelle }} — {{ $classesActives->count() }} classe(s) disponible(s).
                </div>
            @else
                <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-0" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <span class="fw-bolder">Les demandes d’inscription ne sont pas encore ouvertes.</span>
                    L’administration doit d’abord ouvrir une année scolaire et au moins une classe.
                    <a class="fw-bolder" href="{{ route('site.contact') }}">Contactez l’école</a> pour connaître la date d’ouverture.
                </div>
            @endif
        </div>
    </section>

    <section class="py-5">
        <div class="container px-4 px-lg-5 py-4">
            <div class="row gx-5 gy-5">
                <div class="col-lg-6">
                    <h2 class="section-titre h3 mb-3">Conditions d’admission</h2>
                    <ul class="list-unstyled">
                        @foreach ($conditions as $condition)
                            <li class="d-flex mb-2"><i class="bi bi-check2-circle text-primary me-2 mt-1"></i><span class="text-muted">{{ $condition }}</span></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-lg-6">
                    <h2 class="section-titre h3 mb-3">Pièces à fournir</h2>
                    <ul class="list-unstyled">
                        @foreach ($pieces as $piece)
                            <li class="d-flex mb-2"><i class="bi bi-file-earmark-text text-primary me-2 mt-1"></i><span class="text-muted">{{ $piece }}</span></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container px-4 px-lg-5 py-5">
            <h2 class="section-titre display-6 mb-4">La démarche, étape par étape</h2>
            <div class="row gx-4 gy-4">
                @foreach ($etapes as $index => $etape)
                    <div class="col-md-6 col-xl-3">
                        <div class="card h-100 border-0 rounded-4 carte-douce">
                            <div class="card-body p-4">
                                <span class="step-numero mb-3">{{ $index + 1 }}</span>
                                <h3 class="h6 fw-bolder mt-3 mb-1">{{ $etape['titre'] }}</h3>
                                <p class="small text-muted mb-0">{{ $etape['texte'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container px-4 px-lg-5 py-5">
            <h2 class="section-titre display-6 mb-2">Classes ouvertes</h2>
            <p class="text-muted mb-4">Les classes proposées cette année, regroupées par cycle d’enseignement.</p>
            @forelse ($cycles as $cycle)
                <div class="mb-4">
                    <h3 class="h6 fw-bolder text-uppercase text-muted mb-2">{{ $cycle->nom }}</h3>
                    @if ($cycle->classes->isEmpty())
                        <p class="small text-muted mb-0">Aucune classe ouverte dans ce cycle.</p>
                    @else
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($cycle->classes as $classe)
                                <span class="badge bg-white text-dark border rounded-pill px-3 py-2">
                                    {{ $classe->nom }}
                                    @if ($classe->option?->nom) <span class="text-muted">· {{ $classe->option->nom }}</span> @endif
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="alert alert-light border rounded-4 mb-0">La liste des classes sera publiée dès l’ouverture de l’année scolaire.</div>
            @endforelse
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container px-4 px-lg-5 py-5">
            <div class="row gx-5 gy-4">
                <div class="col-lg-7">
                    <h2 class="section-titre h3 mb-3">Frais scolaires &amp; modalités de paiement</h2>
                    @if ($fraisParClasse->isNotEmpty())
                        <p class="text-muted">
                            Frais scolaires définis par l’école pour l’année {{ $anneeEnCours?->libelle ?? 'en cours' }},
                            cumulés par classe. Le règlement se fait au bureau de l’école, contre reçu.
                        </p>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Classe</th><th class="text-end">Frais exigés</th></tr></thead>
                                <tbody>
                                    @foreach ($fraisParClasse as $ligne)
                                        <tr>
                                            <td>{{ $ligne['classe'] }}</td>
                                            <td class="text-end fw-bolder">{{ \App\Support\Monnaie::format($ligne['total'], $ligne['devise']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">
                            Le montant des frais scolaires dépend du cycle et de la classe. Il est communiqué par l’administration
                            au moment de l’inscription définitive, puis suivi par élève dans l’application (encaissements,
                            reçus et situation de recouvrement).
                        </p>
                    @endif
                    <ul class="list-unstyled text-muted">
                        <li class="d-flex mb-2"><i class="bi bi-cash-coin text-primary me-2 mt-1"></i><span>Paiement au bureau de l’école, contre reçu numéroté.</span></li>
                        <li class="d-flex mb-2"><i class="bi bi-shield-lock text-primary me-2 mt-1"></i><span>Aucune transaction financière n’est effectuée depuis les pages publiques de ce site.</span></li>
                        <li class="d-flex mb-2"><i class="bi bi-question-circle text-primary me-2 mt-1"></i><span>Pour un échelonnement, adressez une demande écrite à l’administration.</span></li>
                    </ul>
                </div>
                <div class="col-lg-5">
                    <div class="card border-0 rounded-4 bg-gradient-primary-to-secondary text-white h-100">
                        <div class="card-body p-4 p-lg-5">
                            <h3 class="h5 fw-bolder mb-3">Besoin d’un renseignement avant d’introduire la demande&nbsp;?</h3>
                            <p class="mb-4 opacity-75">L’administration répond aux questions sur les places disponibles, les frais et les pièces à fournir.</p>
                            <a class="btn btn-light px-4 rounded-pill fw-bolder" href="{{ route('site.contact') }}">Écrire à l’école</a>
                            @if ($systemSettings->telephone)
                                <p class="small mt-3 mb-0"><i class="bi bi-telephone me-1"></i>{{ $systemSettings->telephone }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container px-4 px-lg-5 py-5">
            <h2 class="section-titre display-6 mb-4">Questions fréquentes</h2>
            <div class="accordion accordion-flush rounded-4 overflow-hidden carte-douce" id="faqInscriptions">
                @foreach ($faq as $index => $item)
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="faqTitre{{ $index }}">
                            <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }} fw-bolder" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faqCorps{{ $index }}"
                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="faqCorps{{ $index }}">
                                {{ $item['question'] }}
                            </button>
                        </h3>
                        <div id="faqCorps{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                             aria-labelledby="faqTitre{{ $index }}" data-bs-parent="#faqInscriptions">
                            <div class="accordion-body text-muted small">{{ $item['reponse'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container px-4 px-lg-5 py-5">
            <div class="row gx-4 gy-4">
                <div class="col-lg-6">
                    <div class="card h-100 border-0 rounded-4 carte-douce">
                        <div class="card-body p-4 p-lg-5">
                            <i class="bi bi-person-plus fs-2 text-primary"></i>
                            <h3 class="h5 fw-bolder mt-3">Inscription d’un élève</h3>
                            <p class="text-muted small">Formulaire destiné aux parents et responsables légaux : identité de l’élève, classe souhaitée et coordonnées.</p>
                            <a class="btn btn-primary px-4 rounded-pill fw-bolder" href="{{ route('registration.student.create') }}">Introduire la demande</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100 border-0 rounded-4 carte-douce">
                        <div class="card-body p-4 p-lg-5">
                            <i class="bi bi-person-badge fs-2 text-primary"></i>
                            <h3 class="h5 fw-bolder mt-3">Compte du personnel</h3>
                            <p class="text-muted small">Titulaires, professeurs, préfets et comptables : demandez un accès nominatif à l’espace sécurisé.</p>
                            <a class="btn btn-outline-dark px-4 rounded-pill fw-bolder" href="{{ route('registration.account.create') }}">Demander un compte</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
