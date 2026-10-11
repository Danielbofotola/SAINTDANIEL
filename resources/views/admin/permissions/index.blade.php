<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Matrice des permissions</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <style>
        .matrice th, .matrice td { vertical-align: middle; }
        .matrice th.role { text-align: center; }
        .niveau { display: inline-block; padding: 2px 8px; border-radius: 10px; font-weight: 600; font-size: .75rem; }
        .niveau--non { background: #e2e8f0; color: #475569; }
        .niveau--oui, .niveau--total { background: #107c41; color: #fff; }
        .niveau--lecture, .niveau--avis, .niveau--suivi, .niveau--validation, .niveau--rapports { background: #f59e0b; color: #1e293b; }
    </style>
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-lock"></i> Matrice des permissions</h1>
                <p>Qui peut faire quoi — fonctionnalité par poste. Tout niveau autre que « Non » autorise la consultation et la modification de l'écran.</p>
            </div>
            <div><a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a></div>
        </div>

        <div class="tile">
            <div class="alert alert-info">
                Le rôle <strong>Administrateur</strong> (super-administrateur) n'apparaît pas dans la matrice : il a tous les droits.
                Source unique : <code>config/permissions.php</code>, appliquée par le middleware <code>permission</code>.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered matrice">
                    <thead>
                        <tr>
                            <th>Fonctionnalité / Action</th>
                            @foreach ($roles as $role)
                                <th class="role">{{ \App\Models\User::libelleRole($role) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($features as $code => $libelle)
                            <tr>
                                <td>{{ $libelle }}</td>
                                @foreach ($roles as $role)
                                    @php($niveau = \App\Support\Permissions::niveau($role, $code))
                                    <td class="text-center">
                                        <span class="niveau niveau--{{ $niveau }}">{{ \App\Support\Permissions::libelle($niveau) }}</span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
