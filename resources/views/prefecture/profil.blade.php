@extends('prefecture.layout')

@section('titre', 'Mon profil — Espace préfecture')

@section('actions')
    <div class="btn-group btn-group-page-header">
        <button type="button" class="btn btn-light btn-round" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimer
        </button>
    </div>
@endsection

@section('contenu')
    @php
        $nomComplet = trim(($user->nom ?? '').' '.($user->postnom ?? '').' '.($user->prenom ?? '')) ?: $user->username;
        $initial = mb_strtoupper(mb_substr($user->prenom ?: $user->username, 0, 1));
        $joursDepuis = $user->created_at ? (int) $user->created_at->diffInDays(now()) : 0;
    @endphp

    {{-- Carte d'identité --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-profile">
                <div class="card-header" style="background-image: url('{{ asset('azzara/img/blogpost.jpg') }}')">
                    <div class="profile-picture">
                        <div class="avatar avatar-xl">
                            @if ($user->photo)
                                <img src="{{ asset('storage/'.$user->photo) }}" alt="{{ $nomComplet }}" class="avatar-img rounded-circle">
                            @else
                                <span class="avatar-title rounded-circle bg-primary" style="font-size:2rem">{{ $initial }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="user-profile text-center">
                        <div class="name">{{ $nomComplet }}</div>
                        <div class="job">Préfecture — Supervision de l'établissement</div>
                        <div class="mb-2">
                            <span class="badge badge-primary">{{ \App\Models\User::libelleRole($user->role) }}</span>
                            <span class="badge {{ $user->statut === 'actif' ? 'badge-success' : 'badge-danger' }}">{{ $user->statut }}</span>
                            @if ($user->pin_code)
                                <span class="badge badge-info">Code PIN défini</span>
                            @endif
                        </div>
                        <div class="desc">{{ $user->email }}</div>
                        <div class="social-media">
                            <a href="{{ route('prefecture.profil') }}" class="btn btn-info btn-twitter btn-sm btn-link"><i class="fas fa-id-card"></i> Profil</a>
                            <a href="{{ route('notifications.index') }}" class="btn btn-primary btn-sm btn-link"><i class="far fa-bell"></i> Notifications</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chiffres --}}
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small"><i class="far fa-bell"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Notifications reçues</p>
                                <h4 class="card-title">{{ $notificationsTotal }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center {{ $notificationsNonLues > 0 ? 'icon-danger' : 'icon-success' }} bubble-shadow-small">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Non lues</p>
                                <h4 class="card-title">{{ $notificationsNonLues }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small"><i class="far fa-calendar-alt"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Année en cours</p>
                                <h4 class="card-title" style="font-size:1.1rem">{{ $schoolYear?->libelle ?? '—' }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-secondary bubble-shadow-small"><i class="fas fa-hourglass-half"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Compte actif depuis</p>
                                <h4 class="card-title">{{ $joursDepuis }} j</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Identité --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-id-badge text-primary"></i> Identité</div></div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr><th style="width:45%">Nom</th><td>{{ $user->nom ?: '—' }}</td></tr>
                        <tr><th>Post-nom</th><td>{{ $user->postnom ?: '—' }}</td></tr>
                        <tr><th>Prénom</th><td>{{ $user->prenom ?: '—' }}</td></tr>
                        <tr><th>Date de naissance</th><td>{{ $user->date_naissance?->format('d/m/Y') ?? '—' }}</td></tr>
                        <tr><th>Téléphone</th><td>{{ $user->telephone ?: '—' }}</td></tr>
                        <tr><th>Adresse</th><td>{{ $user->adresse ?: '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Compte --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-user-shield text-success"></i> Compte</div></div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr><th style="width:45%">Identifiant</th><td>{{ $user->username }}</td></tr>
                        <tr><th>Adresse e-mail</th><td>{{ $user->email }}</td></tr>
                        <tr><th>Rôle</th><td>{{ \App\Models\User::libelleRole($user->role) }}</td></tr>
                        <tr><th>Statut</th><td>
                            <span class="badge {{ $user->statut === 'actif' ? 'badge-success' : 'badge-danger' }}">{{ $user->statut }}</span>
                        </td></tr>
                        <tr><th>Code PIN</th><td>{{ $user->pin_code ? 'Défini' : 'Non défini' }}</td></tr>
                        <tr><th>Compte créé le</th><td>{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                        <tr><th>Dernière connexion</th><td>{{ $user->last_login?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Périmètre du rôle --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-sitemap text-warning"></i> Périmètre du rôle Préfecture</div></div>
                <div class="card-body">
                    <p class="text-muted">Votre compte combine <strong>consultation</strong> et <strong>décisions de supervision</strong> : vous ne saisissez pas les données scolaires réservées à l'administration, mais vous validez et arbitrez la vie scolaire.</p>

                    <h6 class="fw-bold text-primary">Consultation</h6>
                    <ul class="list-unstyled mb-3">
                        <li class="pb-1"><i class="fas fa-check text-success"></i> Effectifs de l'année en cours (par cycle, par classe, par sexe)</li>
                        <li class="pb-1"><i class="fas fa-check text-success"></i> Organisation des classes (cycle, section, option, salle)</li>
                        <li class="pb-1"><i class="fas fa-check text-success"></i> Horaire des cours et charge des enseignants</li>
                        <li class="pb-1"><i class="fas fa-check text-success"></i> Présence &amp; ponctualité (absences, retards, motifs)</li>
                        <li class="pb-1"><i class="fas fa-check text-success"></i> Dossiers élèves : identité, santé, documents, tuteur, assiduité</li>
                        <li class="pb-1"><i class="fas fa-check text-success"></i> Tuteurs &amp; parents et comptes parents rattachés</li>
                        <li class="pb-1"><i class="fas fa-check text-success"></i> Notes et bulletins par période et par classe</li>
                        <li class="pb-1"><i class="fas fa-check text-success"></i> Personnel : enseignants, comptes utilisateurs, documents administratifs</li>
                        <li class="pb-1"><i class="fas fa-check text-success"></i> Notifications et messagerie interne avec le personnel</li>
                    </ul>

                    <h6 class="fw-bold text-primary">Décisions de supervision</h6>
                    <ul class="list-unstyled mb-3">
                        <li class="pb-1"><i class="fas fa-gavel text-warning"></i> Statut disciplinaire d'un élève : actif, suspendu ou exclu</li>
                        <li class="pb-1"><i class="fas fa-clipboard-check text-warning"></i> Validation des évaluations programmées par les enseignants</li>
                        <li class="pb-1"><i class="fas fa-stamp text-warning"></i> Approbation des bulletins</li>
                        <li class="pb-1"><i class="fas fa-lock-open text-warning"></i> Ouverture / fermeture des périodes d'évaluation</li>
                    </ul>

                    <h6 class="fw-bold text-primary">Hors périmètre</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="pb-1"><i class="fas fa-times text-danger"></i> Saisie des notes, des inscriptions, des paiements et des frais</li>
                        <li class="pb-1"><i class="fas fa-times text-danger"></i> Création ou modification des classes, professeurs et comptes utilisateurs</li>
                        <li class="pb-1"><i class="fas fa-times text-danger"></i> Paramétrage de l'établissement et des modules</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Aperçu de l'établissement + dernières notifications --}}
        <div class="col-md-6">
            <div class="card card-info bg-info-gradient">
                <div class="card-body">
                    <h4 class="mb-3 fw-bold">L'établissement en un coup d'œil</h4>
                    <div class="row text-center">
                        <div class="col-3"><h3 class="mb-0 fw-bold">{{ $effectifTotal }}</h3><small>Élèves</small></div>
                        <div class="col-3"><h3 class="mb-0 fw-bold">{{ $classesActives }}</h3><small>Classes</small></div>
                        <div class="col-3"><h3 class="mb-0 fw-bold">{{ $professeursActifs }}</h3><small>Professeurs</small></div>
                        <div class="col-3"><h3 class="mb-0 fw-bold">{{ $comptesTotal }}</h3><small>Comptes</small></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title"><i class="far fa-bell text-info"></i> Dernières notifications</div>
                        <a href="{{ route('notifications.index') }}" class="btn btn-link btn-sm">Tout voir</a>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($dernieresNotifications as $notification)
                        @php($donnees = $notification->data)
                        <div class="py-2" style="border-bottom:1px solid #eef0f3">
                            <div class="d-flex justify-content-between">
                                <strong class="fs-7">{{ $donnees['titre'] ?? 'Notification' }}</strong>
                                <small class="text-muted">{{ $notification->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                            <small class="text-muted">{{ \Illuminate\Support\Str::limit($donnees['message'] ?? '', 90) }}</small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune notification.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
