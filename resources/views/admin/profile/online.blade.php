<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Utilisateurs en ligne | {{ $systemSettings?->nom_court ?? 'Établissement' }}</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>
        .avatar { width:40px; height:40px; border-radius:50%; background:#795548; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:600; object-fit:cover; }
        .point-en-ligne { display:inline-block; width:9px; height:9px; border-radius:50%; background:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.18); }
    </style>
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-circle text-success"></i> Utilisateurs en ligne</h1>
                <p>
                    Sessions actives des {{ $fenetre }} dernières minutes —
                    {{ $connectes->count() }} compte(s) connecté(s)
                    @if ($sessions->where('est_connecte', false)->count())
                        · {{ $sessions->where('est_connecte', false)->count() }} session(s) invitée(s)
                    @endif
                    · page actualisée automatiquement toutes les 60 s.
                </p>
            </div>
            <div>
                <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Tableau de bord</a>
                <a class="btn btn-info" href="{{ route('admin.profile.accounts') }}"><i class="fa fa-users"></i> Tous les comptes</a>
                <a class="btn btn-primary" href="{{ route('admin.profile.online') }}"><i class="fa fa-refresh"></i> Rafraîchir</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3"><div class="widget-small primary coloured-icon"><i class="icon fa fa-plug fa-3x"></i><div class="info"><h4>Sessions actives</h4><p><b>{{ $sessions->count() }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small success coloured-icon" style="background:#e8f8ee"><i class="icon fa fa-users fa-3x"></i><div class="info"><h4>Comptes connectés</h4><p><b>{{ $connectes->count() }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small info coloured-icon"><i class="icon fa fa-user-secret fa-3x"></i><div class="info"><h4>Invités (non connectés)</h4><p><b>{{ $sessions->where('est_connecte', false)->count() }}</b></p></div></div></div>
            <div class="col-md-3"><div class="widget-small warning coloured-icon"><i class="icon fa fa-clock-o fa-3x"></i><div class="info"><h4>Fenêtre d’activité</h4><p><b>{{ $fenetre }} min</b></p></div></div></div>
        </div>

        <div class="tile">
            <h4 class="tile-title">Personnes connectées</h4>
            @if ($connectes->isEmpty())
                <p class="text-muted mb-0">Aucun compte n’est connecté pour l’instant.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px">État</th>
                                <th>Utilisateur</th>
                                <th>Rôle</th>
                                <th>Adresse e-mail</th>
                                <th>Adresse IP</th>
                                <th>Appareil</th>
                                <th style="width:150px">Dernière activité</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($connectes as $session)
                                @php($compte = $session->user)
                                @php($nom = trim(($compte?->nom.' '.$compte?->postnom.' '.$compte?->prenom)) ?: $compte?->username)
                                <tr>
                                    <td><span class="point-en-ligne" title="En ligne"></span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if ($compte?->photo)
                                                <img class="avatar mr-2" src="{{ asset('storage/'.$compte->photo) }}" alt="">
                                            @else
                                                <span class="avatar mr-2">{{ mb_strtoupper(mb_substr($compte?->prenom ?: $compte?->username, 0, 1)) }}</span>
                                            @endif
                                            <div>
                                                <strong>{{ $nom }}</strong><br>
                                                <small class="text-muted">{{ $compte?->username }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-info">{{ ucfirst($compte?->role ?? '—') }}</span></td>
                                    <td>{{ $compte?->email ?? '—' }}</td>
                                    <td>{{ $session->ip }}</td>
                                    <td>{{ $session->appareil }}</td>
                                    <td>{{ $session->minutes <= 0 ? 'à l’instant' : 'il y a '.$session->minutes.' min' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($sessions->where('est_connecte', false)->isNotEmpty())
            <div class="tile">
                <h4 class="tile-title">Sessions invitées (page de connexion, visiteurs)</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead><tr><th>Adresse IP</th><th>Appareil</th><th style="width:150px">Dernière activité</th></tr></thead>
                        <tbody>
                            @foreach ($sessions->where('est_connecte', false) as $session)
                                <tr>
                                    <td>{{ $session->ip }}</td>
                                    <td>{{ $session->appareil }}</td>
                                    <td>{{ $session->minutes <= 0 ? 'à l’instant' : 'il y a '.$session->minutes.' min' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </main>

    <script>
        // Rechargement automatique de la liste toutes les 60 secondes.
        setTimeout(function () { window.location.reload(); }, 60000);
    </script>
</body>
</html>
