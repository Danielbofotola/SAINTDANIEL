<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Poste de travail — {{ \App\Models\User::libelleRole($user->role) }}</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <style>
        .niveau { display: inline-block; padding: 2px 8px; border-radius: 10px; font-weight: 600; font-size: .72rem; }
        .niveau--non { background: #e2e8f0; color: #475569; }
        .niveau--oui, .niveau--total { background: #107c41; color: #fff; }
        .niveau--lecture, .niveau--avis, .niveau--suivi, .niveau--validation, .niveau--rapports { background: #f59e0b; color: #1e293b; }
    </style>
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-briefcase"></i> Poste de travail</h1>
                <p>
                    {{ trim(($user->prenom ?? '').' '.($user->nom ?? '')) ?: $user->username }}
                    — <strong>{{ \App\Models\User::libelleRole($user->role) }}</strong>
                </p>
            </div>
            <div><a href="{{ route('profile.show') }}" class="btn btn-secondary">Mon profil</a></div>
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-list-check"></i> Vos droits selon la matrice</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>Fonctionnalité / Action</th><th class="text-center">Niveau</th><th class="text-center">Accès</th></tr></thead>
                    <tbody>
                        @foreach ($features as $code => $libelle)
                            @php($niveau = $droits[$code] ?? 'non')
                            <tr class="{{ $niveau === 'non' ? 'text-muted' : '' }}">
                                <td>{{ $libelle }}</td>
                                <td class="text-center"><span class="niveau niveau--{{ $niveau }}">{{ \App\Support\Permissions::libelle($niveau) }}</span></td>
                                <td class="text-center">
                                    @if ($niveau === 'non')
                                        <i class="fa fa-times text-danger" title="Aucun accès"></i>
                                    @else
                                        <i class="fa fa-check text-success" title="Consultation et modification"></i>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
