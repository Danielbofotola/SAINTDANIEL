<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clôture &amp; archives</title>
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
                <h1><i class="fa fa-archive"></i> Clôture &amp; archives</h1>
                <p>Figez la synthèse financière d'une année scolaire : effectifs, recettes, dépenses et solde.</p>
            </div>
            <div>
                <a href="{{ route('admin.depenses.index') }}" class="btn btn-secondary">Dépenses</a>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary"><i class="fa fa-dashboard"></i> Tableau de bord</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-lock"></i> Clôturer une année scolaire</h4>
            @if ($anneesDisponibles->isEmpty())
                <p class="text-muted mb-0">Toutes les années scolaires sont déjà clôturées.</p>
            @else
                <form method="POST" action="{{ route('admin.archives.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 form-group"><label class="form-label">Année scolaire *</label><select class="form-control" name="annee_scolaire_id" required><option value="">Choisir une année</option>@foreach ($anneesDisponibles as $annee)<option value="{{ $annee->id }}" @selected(old('annee_scolaire_id') == $annee->id)>{{ $annee->libelle }}@if($annee->is_encours) (en cours)@endif</option>@endforeach</select></div>
                        <div class="col-md-6 form-group"><label class="form-label">Remarques</label><input class="form-control" name="remarques" value="{{ old('remarques') }}" maxlength="2000" placeholder="Observations de clôture (facultatif)"></div>
                        <div class="col-md-2 form-group d-flex align-items-end"><button class="btn btn-primary btn-block" type="submit"><i class="fa fa-archive"></i> Clôturer</button></div>
                    </div>
                </form>
            @endif
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-list"></i> Années clôturées</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Année</th><th>Élèves inscrits</th><th>Recettes</th><th>Dépenses</th><th>Salaires</th><th>Solde</th><th>Impayés</th><th>Bulletins</th><th>Clôturée le</th><th>Remarques</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($archives as $archive)
                            @php($solde = (float) $archive->solde_final)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $archive->anneeScolaire?->libelle ?? '—' }}</strong></td>
                                <td>{{ $archive->total_eleves_inscrits }}</td>
                                <td>{{ number_format((float) $archive->total_recettes, 2, ',', ' ') }}</td>
                                <td>{{ number_format((float) $archive->total_depenses, 2, ',', ' ') }}</td>
                                <td>{{ number_format((float) $archive->total_salaires, 2, ',', ' ') }}</td>
                                <td><span class="badge badge-{{ $solde >= 0 ? 'success' : 'danger' }}">{{ number_format($solde, 2, ',', ' ') }}</span></td>
                                <td>{{ number_format((float) $archive->total_impayes, 2, ',', ' ') }}</td>
                                <td>{{ $archive->total_bulletins }}</td>
                                <td>{{ $archive->date_cloture?->format('d/m/Y H:i') }}</td>
                                <td>{{ $archive->remarques ?: '—' }}</td>
                                <td>
                                    <a href="{{ route('admin.archives.show', $archive) }}" class="btn btn-sm btn-info" title="Consulter l'année clôturée"><i class="fa fa-folder-open"></i> Consulter</a>
                                    <form class="d-inline" method="POST" action="{{ route('admin.archives.destroy', $archive) }}" onsubmit="return confirm('Rouvrir cette année (supprimer l’archive) ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-warning" type="submit" title="Rouvrir l'année"><i class="fa fa-unlock"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="text-center text-muted">Aucune année clôturée pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
