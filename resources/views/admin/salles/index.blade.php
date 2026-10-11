<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Liste des salles</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-university"></i> Salles disponibles</h1>
                <p>Consultez et gérez les salles de l'établissement.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.salles.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Nouvelle salle</a>
            </div>
        </div>

        <div class="tile">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($sallesARisque->isNotEmpty())
                <div class="alert alert-warning">
                    <strong><i class="fa fa-exclamation-triangle"></i> Locaux à surveiller @if($annee)({{ $annee->libelle }})@endif :</strong>
                    @foreach ($sallesARisque as $salleRisque)
                        @php($o = $occupations[$salleRisque->id])
                        <span class="badge badge-{{ $o['niveau'] === 'saturee' ? 'danger' : 'warning' }} mr-1 mb-1">
                            {{ $salleRisque->nom }} — {{ $o['effectif'] }}/{{ $o['capacite'] }} ({{ $o['taux'] }} %)
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr><th>#</th><th>Nom</th><th>Capacité</th><th>Effectif</th><th>Occupation</th><th>Statut</th><th class="text-center">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($salles as $salle)
                            @php($o = $occupations[$salle->id])
                            <tr class="{{ $o['niveau'] === 'saturee' ? 'table-danger' : ($o['niveau'] === 'risque' ? 'table-warning' : '') }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $salle->nom }}</td>
                                <td>{{ $salle->capacite ?? 'Non définie' }}</td>
                                <td>{{ $o['effectif'] }}</td>
                                <td>
                                    @if ($o['taux'] !== null)
                                        <span class="badge badge-{{ ['ok' => 'success', 'risque' => 'warning', 'saturee' => 'danger'][$o['niveau']] ?? 'secondary' }}">{{ $o['taux'] }} %</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    <small class="d-block text-muted">{{ $o['libelle'] }}</small>
                                </td>
                                <td><span class="badge badge-{{ $salle->statut === 'actif' ? 'success' : 'secondary' }}">{{ $salle->statut === 'actif' ? 'Active' : 'Inactive' }}</span></td>
                                <td class="text-center">
                                    <a href="{{ route('admin.salles.edit', $salle) }}" class="btn btn-sm btn-info" title="Modifier"><i class="fa fa-pencil"></i></a>
                                    <form class="d-inline" method="POST" action="{{ route('admin.salles.toggle-status', $salle) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-{{ $salle->statut === 'actif' ? 'warning' : 'success' }}" type="submit" title="{{ $salle->statut === 'actif' ? 'Désactiver' : 'Activer' }}"><i class="fa fa-power-off"></i></button>
                                    </form>
                                    <form class="d-inline" method="POST" action="{{ route('admin.salles.destroy', $salle) }}" onsubmit="return confirm('Voulez-vous vraiment supprimer cette salle ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="text-center" colspan="7">Aucune salle enregistrée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>