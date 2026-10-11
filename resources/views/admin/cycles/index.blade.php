<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cycles</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-sitemap"></i> Cycles</h1>
                <p>Définissez les niveaux d'enseignement (Primaire, Secondaire…) auxquels rattacher les classes.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-primary"><i class="fa fa-list"></i> Liste des classes</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-plus"></i> Nouveau cycle</h4>
            <form class="row" method="POST" action="{{ route('admin.cycles.store') }}">
                @csrf
                <div class="col-md-9 form-group mb-md-0"><input class="form-control" name="nom" value="{{ old('nom') }}" maxlength="50" required placeholder="Ex. Primaire, Secondaire, Maternel…"></div>
                <div class="col-md-3"><button class="btn btn-primary btn-block" type="submit"><i class="fa fa-save"></i> Créer le cycle</button></div>
            </form>
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-list"></i> Cycles enregistrés</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Cycle</th><th>Classes</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($cycles as $cycle)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $cycle->nom }}</strong></td>
                                <td><span class="badge badge-info">{{ $cycle->classes_count }}</span></td>
                                <td><span class="badge badge-{{ $cycle->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($cycle->statut) }}</span></td>
                                <td class="text-nowrap">
                                    <form class="d-inline" method="POST" action="{{ route('admin.cycles.toggle-status', $cycle) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-{{ $cycle->statut === 'actif' ? 'warning' : 'success' }}" type="submit" title="{{ $cycle->statut === 'actif' ? 'Désactiver' : 'Activer' }}"><i class="fa fa-power-off"></i></button>
                                    </form>
                                    <form class="d-inline" method="POST" action="{{ route('admin.cycles.destroy', $cycle) }}" onsubmit="return confirm('Supprimer ce cycle ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Aucun cycle enregistré.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
