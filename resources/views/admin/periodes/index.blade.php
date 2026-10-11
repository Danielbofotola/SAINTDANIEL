<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Périodes</title>
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
                <h1><i class="fa fa-calendar"></i> Périodes</h1>
                <p>Découpez l'année scolaire en périodes d'évaluation @if($annee) — année <strong>{{ $annee->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.evaluations.index') }}" class="btn btn-primary"><i class="fa fa-pencil"></i> Évaluations</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @unless ($annee)<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Aucune année scolaire en cours. Définissez-en une dans « Années scolaires ».</div>@endunless

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-plus"></i> Nouvelle période</h4>
            <form class="row" method="POST" action="{{ route('admin.periodes.store') }}">
                @csrf
                <div class="col-md-4 form-group mb-md-0"><input class="form-control" name="nom" value="{{ old('nom') }}" maxlength="50" required placeholder="Ex. 1ère Période…"></div>
                <div class="col-md-3 form-group mb-md-0"><label class="form-label">Début <small class="text-muted">(auto)</small></label><input class="form-control" type="text" data-date-fr name="date_debut" value="{{ old('date_debut') }}"></div>
                <div class="col-md-3 form-group mb-md-0"><label class="form-label">Fin <small class="text-muted">(auto)</small></label><input class="form-control" type="text" data-date-fr name="date_fin" value="{{ old('date_fin') }}"></div>
                <div class="col-md-2"><button class="btn btn-primary btn-block" type="submit" @disabled(! $annee)><i class="fa fa-save"></i> Créer</button></div>
            </form>
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-list"></i> Périodes de l'année @if($annee)<small class="text-muted">{{ $annee->libelle }}</small>@endif</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Période</th><th>Évaluations</th><th>Bulletins</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($periodes as $periode)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $periode->nom }}</strong></td>
                                <td><span class="badge badge-info">{{ $periode->evaluations_count }}</span></td>
                                <td><span class="badge badge-info">{{ $periode->bulletins_count }}</span></td>
                                <td><span class="badge badge-{{ $periode->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($periode->statut) }}</span></td>
                                <td class="text-nowrap">
                                    <form class="d-inline" method="POST" action="{{ route('admin.periodes.toggle-status', $periode) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-{{ $periode->statut === 'actif' ? 'warning' : 'success' }}" type="submit" title="{{ $periode->statut === 'actif' ? 'Désactiver' : 'Activer' }}"><i class="fa fa-power-off"></i></button>
                                    </form>
                                    <form class="d-inline" method="POST" action="{{ route('admin.periodes.destroy', $periode) }}" onsubmit="return confirm('Supprimer cette période ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucune période définie pour l'année en cours.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
