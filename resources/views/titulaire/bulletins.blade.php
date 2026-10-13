<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173f3b">
    <title>Bulletins de classe | {{ $systemSettings->nom_court }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    @include('partials.role-space-styles')
</head>
<body>
    @php
        $displayName = $professor ? trim($professor->prenom.' '.$professor->nom) : (trim(($user->prenom ?: '').' '.($user->nom ?: '')) ?: $user->username);
        $initial = strtoupper(substr($displayName, 0, 1));
        $photo = $user->photo ?: $professor?->photo;
    @endphp
    <div class="teacher-shell">
        <aside class="teacher-sidebar" aria-label="Navigation principale">
            <a class="brand" href="{{ route('titulaire.dashboard') }}">
                <span class="brand-mark">M</span>
                <span class="brand-copy"><span class="brand-name">{{ $systemSettings->nom_court }}</span><span class="brand-caption">Espace scolaire</span></span>
            </a>
            <div class="nav-caption">Espace titulaire</div>
            <nav class="teacher-nav">
                <a href="{{ route('titulaire.dashboard') }}"><i class="fa fa-th-large"></i><span class="nav-label">Vue générale</span></a>
                <a class="active" href="{{ route('titulaire.bulletins.index') }}" aria-current="page"><i class="fa fa-file-text-o"></i><span class="nav-label">Bulletins &amp; synthèse</span></a>
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
                <div class="breadcrumb"><span class="school-brand">{{ $systemSettings->nom_court }}</span> <span aria-hidden="true">/</span> <strong>Bulletins &amp; synthèse</strong></div>
                <div class="topbar-actions">
                    @include('partials.raccourcis', ['raccourcis' => \App\Support\Raccourcis::pour($user), 'variante' => 'clair'])
                    <a class="top-avatar" href="{{ route('profile.show') }}">
                        <span class="avatar">@if ($photo)<img src="{{ asset('storage/'.$photo) }}" alt="">@else{{ $initial }}@endif</span>
                        <span class="top-avatar-copy"><span class="top-avatar-name">{{ $displayName }}</span><span class="top-avatar-role">Titulaire</span></span>
                    </a>
                </div>
            </header>

            <div class="dashboard-content">
                <section class="page-intro">
                    <div>
                        <p class="eyebrow">Suivi pédagogique</p>
                        <h1>Bulletins &amp; synthèse de classe</h1>
                        <p class="intro-subtitle">{{ $annee ? 'Année scolaire '.$annee->libelle : 'Année scolaire non définie' }}</p>
                    </div>
                    <time class="date-stamp" datetime="{{ now()->toDateString() }}"><i class="fa fa-calendar-o" aria-hidden="true"></i> {{ now()->format('d/m/Y') }}</time>
                </section>

                @if (session('success'))<div class="link-notice" role="status"><i class="fa fa-check-circle" aria-hidden="true"></i><div><strong>{{ session('success') }}</strong></div></div>@endif
                @if (session('error'))<div class="link-notice" role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i><div><strong>{{ session('error') }}</strong></div></div>@endif
                @if ($errors->any())<div class="link-notice" role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i><div><strong>{{ $errors->first() }}</strong></div></div>@endif

                @unless ($annee)
                    <div class="link-notice" role="status"><i class="fa fa-info-circle" aria-hidden="true"></i><div><strong>Aucune année scolaire en cours.</strong></div></div>
                @endunless

                <section class="content-section">
                    <div class="section-heading"><div><h2>Sélection</h2><p>Choisissez une période et l'une de vos classes</p></div><span class="section-link">{{ $classes->count() }} classe(s)</span></div>
                    <form class="row" method="GET" action="{{ route('titulaire.bulletins.index') }}" style="gap:12px;align-items:flex-end;display:flex;flex-wrap:wrap">
                        <div style="min-width:220px">
                            <label class="nav-label" for="periode_id">Période</label>
                            <select class="form-control" id="periode_id" name="periode_id" required>
                                <option value="">Choisir une période</option>
                                @foreach ($periodes as $p)
                                    <option value="{{ $p->id }}" @selected((string) $periodeId === (string) $p->id)>{{ $p->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="min-width:220px">
                            <label class="nav-label" for="classe_id">Classe</label>
                            <select class="form-control" id="classe_id" name="classe_id" required>
                                <option value="">Choisir une classe</option>
                                @foreach ($classes as $c)
                                    <option value="{{ $c->id }}" @selected((string) $classeId === (string) $c->id)>{{ $c->nom }}@if ($c->cycle) — {{ $c->cycle->nom }}@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div><button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Afficher</button></div>
                    </form>
                </section>

                @if ($periode && $classe)
                    <section class="content-section">
                        <div class="section-heading">
                            <div><h2>Classement — {{ $classe->nom }}</h2><p>{{ $periode->nom }} · {{ $rows->count() }} élève(s)</p></div>
                            @if ($rows->isNotEmpty())
                                <form method="POST" action="{{ route('titulaire.bulletins.generate') }}">
                                    @csrf
                                    <input type="hidden" name="periode_id" value="{{ $periode->id }}">
                                    <input type="hidden" name="classe_id" value="{{ $classe->id }}">
                                    <button class="btn btn-primary" type="submit"><i class="fa fa-cogs"></i> Générer / actualiser</button>
                                </form>
                            @endif
                        </div>

                        @if ($rows->isNotEmpty())
                            <form method="POST" action="{{ route('titulaire.bulletins.appreciations') }}">
                                @csrf
                                <input type="hidden" name="periode_id" value="{{ $periode->id }}">
                                <input type="hidden" name="classe_id" value="{{ $classe->id }}">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Place</th><th>Élève</th><th>Notes</th><th>Total</th><th>%</th>
                                                <th>Mention</th><th>Conduite</th><th>Application</th><th>Bulletin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($rows as $row)
                                                @php($inscription = $row['inscription'])
                                                <tr>
                                                    <td><span class="badge badge-{{ $row['place'] <= 3 ? 'success' : 'secondary' }}">{{ $row['place'] }}</span></td>
                                                    <td>
                                                        {{ $inscription->eleve?->nom }} {{ $inscription->eleve?->postnom }} {{ $inscription->eleve?->prenom }}
                                                        <br><small class="text-muted">{{ $inscription->eleve?->matricule }}</small>
                                                    </td>
                                                    <td>{{ $row['nb_notes'] }}</td>
                                                    <td>{{ rtrim(rtrim(number_format($row['total'], 2, '.', ''), '0'), '.') }} / {{ rtrim(rtrim(number_format($row['max'], 2, '.', ''), '0'), '.') }}</td>
                                                    <td>{{ number_format($row['pourcentage'], 2) }} %</td>
                                                    <td>{{ $row['mention'] }}</td>
                                                    <td><input class="form-control form-control-sm" style="min-width:110px" name="conduite[{{ $inscription->id }}]" maxlength="50" value="{{ old('conduite.'.$inscription->id, $row['bulletin']?->conduite) }}" placeholder="Ex. Bonne"></td>
                                                    <td><input class="form-control form-control-sm" style="min-width:110px" name="application[{{ $inscription->id }}]" maxlength="50" value="{{ old('application.'.$inscription->id, $row['bulletin']?->application) }}" placeholder="Ex. Régulier"></td>
                                                    <td class="text-nowrap">
                                                        @if ($row['bulletin'])
                                                            <span class="badge badge-success">Généré</span>
                                                        @else
                                                            <span class="badge badge-secondary">Non généré</span>
                                                        @endif
                                                        <a href="{{ route('titulaire.bulletins.document', $inscription) }}" class="btn btn-sm btn-info" target="_blank" rel="noopener" title="Bulletin annuel (norme RDC)"><i class="fa fa-file-text-o"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> Enregistrer les appréciations</button>
                            </form>
                        @else
                            <div class="schedule-panel"><div class="empty-state"><div><i class="fa fa-users" aria-hidden="true"></i><strong>Aucun élève inscrit</strong><span>Aucun élève actif dans cette classe pour l'année en cours.</span></div></div></div>
                        @endif
                    </section>
                @elseif ($annee)
                    <section class="content-section">
                        <div class="schedule-panel"><div class="empty-state"><div><i class="fa fa-file-text-o" aria-hidden="true"></i><strong>Choisissez une période et une classe</strong><span>Le classement et les bulletins de la classe s'afficheront ici.</span></div></div></div>
                    </section>
                @endif

                <p class="footer-note"><span class="school-brand">{{ $systemSettings->nom_court }}</span> <span aria-hidden="true">·</span> Espace titulaire</p>
            </div>
        </main>
    </div>
    @include('partials.cloche-flottante')
</body>
</html>
