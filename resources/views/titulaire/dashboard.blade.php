<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173f3b">
    <title>Espace titulaire | {{ $systemSettings->nom_court }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    @include('partials.role-space-styles')
</head>
<body>
    @php
        $displayName = $professor ? trim($professor->prenom.' '.$professor->nom) : (trim(($user->prenom ?: '').' '.($user->nom ?: '')) ?: $user->username);
        $initial = strtoupper(substr($displayName, 0, 1));
        $photo = $user->photo ?: $professor?->photo;
        $effectifMoyen = $classes->isNotEmpty() ? (int) round($effectif / max($classes->count(), 1)) : 0;
    @endphp
    <div class="teacher-shell">
        <aside class="teacher-sidebar" aria-label="Navigation principale">
            <a class="brand" href="{{ route('titulaire.dashboard') }}">
                <span class="brand-mark">M</span>
                <span class="brand-copy"><span class="brand-name">{{ $systemSettings->nom_court }}</span><span class="brand-caption">Espace scolaire</span></span>
            </a>
            <div class="nav-caption">Espace titulaire</div>
            <nav class="teacher-nav">
                <a class="active" href="{{ route('titulaire.dashboard') }}" aria-current="page"><i class="fa fa-th-large"></i><span class="nav-label">Vue générale</span></a>
                <a href="#classes"><i class="fa fa-graduation-cap"></i><span class="nav-label">Mes classes</span></a>
                <a href="#eleves"><i class="fa fa-users"></i><span class="nav-label">Mes élèves</span></a>
                <a href="{{ route('titulaire.bulletins.index') }}"><i class="fa fa-file-text-o"></i><span class="nav-label">Bulletins &amp; synthèse</span></a>
            </nav>
            <div class="sidebar-bottom">
                <a class="profile-shortcut" href="{{ route('profile.show') }}" title="Ouvrir mon profil">
                    <span class="avatar">@if ($photo)<img src="{{ asset('storage/'.$photo) }}" alt="">@else{{ $initial }}@endif</span>
                    <span><span class="profile-name">{{ $displayName }}</span><span class="profile-role">Titulaire</span></span>
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="sidebar-logout" type="submit"><i class="fa fa-sign-out"></i> <span class="logout-label">Déconnexion</span></button></form>
            </div>
        </aside>

        <main class="teacher-main">
            @include('partials.impersonation-banner')
            <header class="topbar">
                <div class="breadcrumb"><span class="school-brand">{{ $systemSettings->nom_court }}</span> <span aria-hidden="true">/</span> <strong>Espace titulaire</strong></div>
                <div class="topbar-actions">
                    @include('partials.raccourcis', ['raccourcis' => \App\Support\Raccourcis::pour($user), 'variante' => 'clair'])
                    <a class="icon-link" href="{{ route('profile.show') }}" aria-label="Mon profil" title="Mon profil"><i class="fa fa-user-o"></i></a>
                    <a class="top-avatar" href="{{ route('profile.show') }}">
                        <span class="avatar">@if ($photo)<img src="{{ asset('storage/'.$photo) }}" alt="">@else{{ $initial }}@endif</span>
                        <span class="top-avatar-copy"><span class="top-avatar-name">{{ $displayName }}</span><span class="top-avatar-role">Titulaire</span></span>
                    </a>
                </div>
            </header>

            <div class="dashboard-content">
                <section class="page-intro">
                    <div><p class="eyebrow">Votre espace de travail</p><h1>Bonjour, {{ $professor?->prenom ?: $user->prenom ?: $user->username }}</h1><p class="intro-subtitle">{{ $schoolYear ? 'Année scolaire '.$schoolYear->libelle : 'Année scolaire non définie' }}@if ($professor?->specialite) <span aria-hidden="true">·</span> {{ $professor->specialite }}@endif</p></div>
                    <time class="date-stamp" datetime="{{ now()->toDateString() }}"><i class="fa fa-calendar-o" aria-hidden="true"></i> {{ now()->format('d/m/Y') }}</time>
                </section>

                <section class="welcome-banner" aria-label="Bienvenue dans votre espace titulaire">
                    <div class="welcome-copy">
                        <span class="welcome-tag"><i class="fa fa-circle" aria-hidden="true"></i> {{ $schoolYear?->libelle ?: 'Espace titulaire' }}</span>
                        <h2>{{ $classes->isNotEmpty() ? 'Suivi de vos classes' : 'Votre espace de titulaire' }}</h2>
                        <p>{{ $classes->isNotEmpty() ? 'Vous suivez '.$classes->count().' classe(s) pour '.$effectif.' élève(s) cette année.' : 'Vos classes et leurs effectifs apparaîtront ici.' }}</p>
                        <a class="banner-action" href="#classes">Voir mes classes <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                    <span class="banner-art" aria-hidden="true"><i class="fa fa-graduation-cap"></i></span>
                </section>

                @if (! $professor)
                    <div class="link-notice" role="status"><i class="fa fa-info-circle" aria-hidden="true"></i><div><strong>Aucune fiche enseignant n’est reliée à ce compte.</strong><p>Demandez à l’administration de relier votre compte à votre fiche professeur pour afficher vos classes et vos élèves.</p></div></div>
                @elseif (! $schoolYear)
                    <div class="link-notice" role="status"><i class="fa fa-info-circle" aria-hidden="true"></i><div><strong>Aucune année scolaire en cours n’est définie.</strong><p>Vos classes apparaîtront ici lorsque l’administration aura activé l’année scolaire.</p></div></div>
                @elseif ($classes->isEmpty())
                    <div class="link-notice" role="status"><i class="fa fa-info-circle" aria-hidden="true"></i><div><strong>Aucune classe ne vous est rattachée pour cette année.</strong><p>Un titulaire voit les classes où il assure au moins une affectation de cours pour l’année en cours.</p></div></div>
                @endif

                <section class="stat-grid" aria-label="Mes indicateurs de titulaire">
                    <article class="stat-card"><span class="stat-icon"><i class="fa fa-graduation-cap" aria-hidden="true"></i></span><div><div class="stat-value">{{ $classes->count() }}</div><div class="stat-label">Classes suivies</div></div></article>
                    <article class="stat-card"><span class="stat-icon"><i class="fa fa-users" aria-hidden="true"></i></span><div><div class="stat-value">{{ $effectif }}</div><div class="stat-label">Élèves concernés</div></div></article>
                    <article class="stat-card"><span class="stat-icon"><i class="fa fa-book" aria-hidden="true"></i></span><div><div class="stat-value">{{ $nbCours }}</div><div class="stat-label">Cours affectés</div></div></article>
                    <article class="stat-card"><span class="stat-icon"><i class="fa fa-signal" aria-hidden="true"></i></span><div><div class="stat-value">{{ $effectifMoyen }}</div><div class="stat-label">Élèves par classe</div></div></article>
                </section>

                <div class="dashboard-grid">
                    <div>
                        <section class="content-section" id="classes">
                            <div class="section-heading"><div><h2>Mes classes</h2><p>Effectifs de l’année en cours</p></div><span class="section-link">{{ $classes->count() }} classe(s)</span></div>
                            @if ($classes->isNotEmpty())
                                <div class="course-list">
                                    @foreach ($classes as $classe)
                                        <article class="course-row">
                                            <div>
                                                <div class="course-title">{{ $classe->nom }}</div>
                                                <div class="course-details">{{ $classe->cycle?->nom ?? 'Cycle non renseigné' }} · {{ $classe->salle?->nom ?? 'Salle non attribuée' }}</div>
                                            </div>
                                            <span class="course-code">{{ $classe->effectif }} élève(s)</span>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="schedule-panel"><div class="empty-state"><div><i class="fa fa-graduation-cap" aria-hidden="true"></i><strong>Aucune classe à afficher</strong><span>Vos classes titulaires apparaîtront ici pour l’année scolaire en cours.</span></div></div></div>
                            @endif
                        </section>
                    </div>

                    <div class="right-column">
                        <section class="content-section" id="eleves">
                            <div class="section-heading"><div><h2>Mes élèves</h2><p>Inscrits de l’année dans vos classes</p></div><span class="section-link">{{ $effectif }} élève(s)</span></div>
                            @if ($eleves->isNotEmpty())
                                <div class="evaluation-panel">
                                    @foreach ($eleves as $inscription)
                                        <article class="evaluation-item">
                                            <div class="evaluation-copy">
                                                <div class="evaluation-title">{{ $inscription->eleve?->nom }} {{ $inscription->eleve?->postnom }} {{ $inscription->eleve?->prenom }}</div>
                                                <div class="evaluation-meta">{{ $inscription->eleve?->matricule ?? '—' }} · {{ $inscription->classe?->nom ?? 'Classe inconnue' }}</div>
                                            </div>
                                            <span class="room-chip">{{ $inscription->eleve?->sexe === 'F' ? 'Fille' : 'Garçon' }}</span>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="schedule-panel"><div class="empty-state"><div><i class="fa fa-users" aria-hidden="true"></i><strong>Aucun élève inscrit</strong><span>Les élèves inscrits dans vos classes cette année apparaîtront ici.</span></div></div></div>
                            @endif
                        </section>
                        <p class="footer-note"><span class="school-brand">{{ $systemSettings->nom_court }}</span> <span aria-hidden="true">·</span> Espace titulaire</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
@include('partials.cloche-flottante')
</body>
</html>
