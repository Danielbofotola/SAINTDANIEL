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
    <style>
        .fin-form .form-control { display:inline-block; vertical-align:middle; margin-right:6px; }
    </style>
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-sitemap"></i> Cycles</h1>
                <p>Définissez les niveaux d'enseignement (Primaire, Secondaire…) et la fin de chaque cycle : certificat (ex. CEP) ou diplôme.</p>
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
                <div class="col-md-4 form-group"><label class="form-label">Nom *</label><input class="form-control" name="nom" value="{{ old('nom') }}" maxlength="50" required placeholder="Ex. Primaire, Secondaire, Maternel…"></div>
                <div class="col-md-2 form-group"><label class="form-label">Rang</label><input type="number" class="form-control" name="ordre" value="{{ old('ordre') }}" min="1" max="20" title="Rang du cycle dans le parcours (1 = premier)"></div>
                <div class="col-md-3 form-group"><label class="form-label">Fin de cycle</label>
                    <select class="form-control" name="fin_type">
                        @foreach (\App\Models\Cycle::FINS as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected(old('fin_type', 'aucune') === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group"><label class="form-label">Intitulé remis</label><input class="form-control" name="fin_libelle" value="{{ old('fin_libelle') }}" maxlength="100" placeholder="Ex. Certificat d'études primaires"></div>
                <div class="col-12"><button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Créer le cycle</button></div>
            </form>
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-list"></i> Cycles enregistrés</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead><tr><th>#</th><th>Cycle</th><th>Classes</th><th>Fin de cycle (rang · type · intitulé)</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($cycles as $cycle)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $cycle->nom }}</strong>
                                    @if ($cycle->fin_type === 'diplome')
                                        <div><span class="badge badge-info">{{ $cycle->fin_libelle ?: 'Diplôme (fin du parcours)' }}</span></div>
                                    @elseif ($cycle->fin_type === 'certificat')
                                        <div><span class="badge badge-warning">{{ $cycle->fin_libelle ?: 'Certificat de fin de cycle' }}</span></div>
                                    @endif
                                </td>
                                <td><span class="badge badge-secondary">{{ $cycle->classes_count }}</span></td>
                                <td class="text-nowrap">
                                    <form class="fin-form" method="POST" action="{{ route('admin.cycles.update', $cycle) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" class="form-control form-control-sm" style="width:70px" name="ordre" value="{{ $cycle->ordre }}" min="1" max="20" title="Rang du cycle dans le parcours">
                                        <select class="form-control form-control-sm" name="fin_type" style="width:220px">
                                            @foreach (\App\Models\Cycle::FINS as $valeur => $libelle)
                                                <option value="{{ $valeur }}" @selected($cycle->fin_type === $valeur)>{{ $libelle }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" class="form-control form-control-sm" style="width:200px" name="fin_libelle" value="{{ $cycle->fin_libelle }}" maxlength="100" placeholder="Intitulé (ex. CEP)">
                                        <button class="btn btn-sm btn-primary" type="submit" title="Enregistrer"><i class="fa fa-save"></i></button>
                                    </form>
                                </td>
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
                            <tr><td colspan="6" class="text-center text-muted">Aucun cycle enregistré.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
