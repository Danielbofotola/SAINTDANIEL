@extends('professeur.layout')

@section('titre', 'Mon profil')
@section('titre-page')<i class="fa fa-user mr-2"></i> Mon profil @endsection
@section('sous-titre', ($professor?->specialite ?: 'Enseignant').' · '.$affectations->count().' cours')

@section('content')
    <style>
        .profile-top { background:#0d0c0f; color:#fff; border-radius:12px; }
        .profile-top h5 { color:#fff; margin:0; }
        .profile-actions { background:#b26111; border-radius:10px; }
        .profile-actions a { color:#fff; padding:8px 12px; font-size:13px; font-weight:500; text-decoration:none; }
        .profile-actions a:hover { color:#fff; background:rgba(255,255,255,.15); }
        .detail-row { display:flex; margin-bottom:12px; font-size:14px; }
        .detail-label { width:190px; font-weight:600; color:var(--able-text-main); }
        .detail-value { color:#323130; }
        .avatar-lg { width:82px; height:82px; border-radius:50%; background:#795548; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:30px; font-weight:600; object-fit:cover; }
    </style>

    <!-- Bandeau de profil -->
    <div class="profile-top p-3 mb-3 d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center">
            @if ($user->photo || $professor?->photo)
                <img class="avatar-lg mr-3" src="{{ asset('storage/'.($user->photo ?: $professor->photo)) }}" alt="Photo de profil">
            @else
                <span class="avatar-lg mr-3">{{ mb_strtoupper(mb_substr($professor?->prenom ?: $user->prenom ?: $user->username, 0, 1)) }}</span>
            @endif
            <div>
                <h5>{{ strtoupper(trim(($professor?->nom.' '.$professor?->postnom.' '.$professor?->prenom) ?: $user->username)) }}</h5>
                <small class="text-white-50">Enseignant{{ $professor?->matricule ? ' · '.$professor->matricule : '' }}</small>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-light btn-sm" type="submit"><i class="fa fa-sign-out"></i> Déconnexion</button>
        </form>
    </div>

    <!-- Barre d’action -->
    <div class="profile-actions d-flex flex-wrap align-items-center mb-4">
        <a href="{{ route('professeur.profil') }}"><i class="fa fa-user"></i> Mes informations</a>
        <a href="{{ route('professeur.planning') }}"><i class="fa fa-calendar-o"></i> Mon planning</a>
        <a href="{{ route('professeur.cours') }}"><i class="fa fa-book"></i> Mes cours</a>
        <a href="{{ route('professeur.eleves') }}"><i class="fa fa-users"></i> Mes élèves</a>
        <a href="{{ route('professeur.evaluations') }}"><i class="fa fa-pencil-square-o"></i> Évaluations &amp; notes</a>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon primary"><i class="fa fa-book"></i></div><div class="stat-details"><h4>Cours affectés</h4><p>{{ $affectations->count() }}<small>{{ $affectations->pluck('classe_id')->unique()->count() }} classe(s)</small></p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon info"><i class="fa fa-users"></i></div><div class="stat-details"><h4>Élèves suivis</h4><p>{{ number_format((int) $effectifs->sum(), 0, ',', ' ') }}</p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon warning"><i class="fa fa-clock-o"></i></div><div class="stat-details"><h4>Heures / semaine</h4><p>{{ $heuresParSemaine }}<small>{{ $emplois->count() }} créneau(x)</small></p></div></div></div>
        <div class="col-sm-6 col-lg-3 mb-3"><div class="stat-card"><div class="stat-icon danger"><i class="fa fa-pencil-square-o"></i></div><div class="stat-details"><h4>Évaluations</h4><p>{{ $evaluations->count() }}<small>{{ $notesSaisies }} note(s) saisie(s)</small></p></div></div></div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="tile">
                <h3 class="tile-title">Informations de l’enseignant</h3>
                <div class="d-flex align-items-center mb-3">
                    @if ($user->photo || $professor?->photo)
                        <img class="avatar-lg mr-3" style="width:60px;height:60px;font-size:22px" src="{{ asset('storage/'.($user->photo ?: $professor->photo)) }}" alt="">
                    @else
                        <span class="avatar-lg mr-3" style="width:60px;height:60px;font-size:22px">{{ mb_strtoupper(mb_substr($professor?->prenom ?: $user->username, 0, 1)) }}</span>
                    @endif
                    <div><strong>{{ $professor?->nomComplet ?? $user->username }}</strong><br><span class="pill">Enseignant</span></div>
                </div>
                <div class="detail-row"><div class="detail-label">Matricule</div><div class="detail-value">{{ $professor?->matricule ?: 'Non renseigné' }}</div></div>
                <div class="detail-row"><div class="detail-label">Spécialité</div><div class="detail-value">{{ $professor?->specialite ?: 'Non renseignée' }}</div></div>
                <div class="detail-row"><div class="detail-label">Sexe</div><div class="detail-value">{{ $professor?->sexe === 'F' ? 'Féminin' : ($professor?->sexe === 'M' ? 'Masculin' : 'Non renseigné') }}</div></div>
                <div class="detail-row"><div class="detail-label">Téléphone</div><div class="detail-value">{{ $professor?->telephone ?: ($user->telephone ?: 'Non renseigné') }}</div></div>
                <div class="detail-row"><div class="detail-label">E-mail</div><div class="detail-value">{{ $user->email }}</div></div>
                <div class="detail-row"><div class="detail-label">Statut</div><div class="detail-value"><span class="pill">{{ ucfirst($professor?->statut ?? $user->statut) }}</span></div></div>
                <div class="detail-row"><div class="detail-label">Dernière connexion</div><div class="detail-value">{{ $user->last_login?->format('d/m/Y H:i') ?? 'Pas encore connecté' }}</div></div>
                <p class="text-muted mb-0" style="font-size:12px">
                    <i class="fa fa-lock"></i> Vos coordonnées et votre mot de passe sont gérés par l’administration.
                </p>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="tile">
                <h3 class="tile-title">Répartition des heures de cours par matière</h3>
                @if ($heuresParCours->isEmpty())
                    <div class="empty"><i class="fa fa-bar-chart" aria-hidden="true"></i><strong>Aucune heure planifiée</strong>Le graphique s’affichera dès que vos horaires seront saisis.</div>
                @else
                    <canvas id="chartHeures" height="150"></canvas>
                @endif
            </div>
        </div>
    </div>

    <div class="tile">
        <h3 class="tile-title">Mes enseignements &amp; affectations <span class="pill ml-2">{{ $affectations->count() }} cours</span></h3>
        @if ($affectations->isEmpty())
            <div class="empty"><i class="fa fa-book" aria-hidden="true"></i><strong>Aucun cours affecté</strong>Vos cours apparaîtront ici dès que l’administration aura saisi vos affectations.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Code</th>
                            <th>Classe</th>
                            <th class="text-right">Élèves</th>
                            <th class="text-right">Créneaux</th>
                            <th class="text-right">Évaluations</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($affectations as $affectation)
                            <tr>
                                <td><strong>{{ $affectation->cours_nom }}</strong></td>
                                <td>{{ $affectation->cours_code }}</td>
                                <td>{{ $affectation->classe_nom }}@if ($affectation->section_nom) · {{ $affectation->section_nom }}@endif</td>
                                <td class="text-right">{{ $effectifs[$affectation->classe_id] ?? 0 }}</td>
                                <td class="text-right">{{ $emplois->where('affectation_id', $affectation->id)->count() }}</td>
                                <td class="text-right">{{ $evaluations->where('affectation_id', $affectation->id)->count() }}</td>
                                <td class="text-right text-nowrap">
                                    <a class="btn btn-sm btn-primary" href="{{ route('professeur.cours.show', $affectation->id) }}"><i class="fa fa-eye"></i> Ouvrir</a>
                                    <a class="btn btn-sm btn-info" href="{{ route('professeur.presences', ['affectation' => $affectation->id]) }}"><i class="fa fa-check-square-o"></i> Appel</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($heuresParCours->isNotEmpty())
        <script src="{{ asset('boostrap/css/js/plugins/chart.umd.js') }}"></script>
        <script>
            (function () {
                var zone = document.getElementById('chartHeures');
                if (!zone || typeof Chart === 'undefined') { return; }

                new Chart(zone, {
                    type: 'bar',
                    data: {
                        labels: @json($heuresParCours->keys()),
                        datasets: [{ label: 'Heures / semaine', data: @json($heuresParCours->values()), backgroundColor: '#4680ff' }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });
            })();
        </script>
    @endif
@endsection
