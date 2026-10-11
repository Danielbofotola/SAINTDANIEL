<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Évaluations</title>
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
                <h1><i class="fa fa-pencil"></i> Évaluations</h1>
                <p>Créez les interrogations, devoirs et examens puis saisissez les notes @if($annee) — année <strong>{{ $annee->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a href="{{ route('admin.periodes.index') }}" class="btn btn-secondary">Périodes</a>
                <a href="{{ route('admin.bulletins.index') }}" class="btn btn-primary"><i class="fa fa-file-text-o"></i> Bulletins</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @unless ($annee)<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Aucune année scolaire en cours.</div>@endunless
        @unless ($saisieNotesActive)<div class="alert alert-warning"><i class="fa fa-toggle-off"></i> La saisie des notes est désactivée (« Commuter les modules »).</div>@endunless

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-plus"></i> Nouvelle évaluation</h4>
            @if ($affectations->isEmpty() || $periodes->isEmpty())
                <p class="text-muted mb-0">Créez au moins une affectation (« Cours &amp; affectation ») et une période (« Périodes ») avant d'ajouter une évaluation.</p>
            @else
                <form method="POST" action="{{ route('admin.evaluations.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-5 form-group"><label class="form-label">Affectation (classe — cours — professeur) *</label><select class="form-control" name="affectation_id" required><option value="">Choisir une affectation</option>@foreach ($affectations as $affectation)<option value="{{ $affectation->id }}" @selected(old('affectation_id') == $affectation->id)>{{ $affectation->classe?->nom }} — {{ $affectation->cours?->intitule }} — {{ $affectation->professeur?->nom }} {{ $affectation->professeur?->prenom }}</option>@endforeach</select></div>
                        <div class="col-md-3 form-group"><label class="form-label">Période *</label><select class="form-control" name="periode_id" required><option value="">Choisir une période</option>@foreach ($periodes as $periode)<option value="{{ $periode->id }}" @selected(old('periode_id') == $periode->id)>{{ $periode->nom }}</option>@endforeach</select></div>
                        <div class="col-md-4 form-group"><label class="form-label">Titre *</label><input class="form-control" name="titre" value="{{ old('titre') }}" maxlength="100" required placeholder="Ex. Interro n°1"></div>
                        <div class="col-md-3 form-group"><label class="form-label">Type *</label><select class="form-control" name="type_evaluation" required>@foreach ($types as $value => $label)<option value="{{ $value }}" @selected(old('type_evaluation', 'interro') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="col-md-3 form-group"><label class="form-label">Note maximale <small class="text-muted">(auto)</small></label><input class="form-control" type="number" step="0.01" min="1" max="1000" name="max_note" value="{{ old('max_note') }}" placeholder="Barème du cours"><small class="text-muted">Vide = barème du cours (max examen / travaux journaliers).</small></div>
                        <div class="col-md-3 form-group"><label class="form-label">Date *</label><input class="form-control" type="text" data-date-fr name="date_evaluation" value="{{ old('date_evaluation', now()->toDateString()) }}" required></div>
                        <div class="col-md-3 form-group d-flex align-items-end"><button class="btn btn-primary btn-block" type="submit"><i class="fa fa-save"></i> Créer l'évaluation</button></div>
                    </div>
                </form>
            @endif
        </div>

        <div class="tile">
            <h4 class="tile-title"><i class="fa fa-list"></i> Évaluations enregistrées</h4>
            <form class="row mb-3" method="GET" action="{{ route('admin.evaluations.index') }}">
                <div class="col-md-5 form-group mb-md-0"><select class="form-control" name="periode_id"><option value="">Toutes les périodes</option>@foreach ($periodes as $periode)<option value="{{ $periode->id }}" @selected((string) $periodeId === (string) $periode->id)>{{ $periode->nom }}</option>@endforeach</select></div>
                <div class="col-md-5 form-group mb-md-0"><select class="form-control" name="classe_id"><option value="">Toutes les classes</option>@foreach ($classes as $classe)<option value="{{ $classe->id }}" @selected((string) $classeId === (string) $classe->id)>{{ $classe->nom }}</option>@endforeach</select></div>
                <div class="col-md-2"><button class="btn btn-info btn-block" type="submit"><i class="fa fa-filter"></i> Filtrer</button></div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead><tr><th>#</th><th>Date</th><th>Titre</th><th>Type</th><th>Classe</th><th>Cours</th><th>Professeur</th><th>Période</th><th>Max</th><th>Notes</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($evaluations as $evaluation)
                            <tr>
                                <td>{{ $evaluations->firstItem() + $loop->index }}</td>
                                <td>{{ $evaluation->date_evaluation?->format('d/m/Y') }}</td>
                                <td>{{ $evaluation->titre }}</td>
                                <td><span class="badge badge-info">{{ $types[$evaluation->type_evaluation] ?? ucfirst($evaluation->type_evaluation) }}</span></td>
                                <td>{{ $evaluation->affectation?->classe?->nom }}</td>
                                <td>{{ $evaluation->affectation?->cours?->intitule }}</td>
                                <td>{{ $evaluation->affectation?->professeur?->nom }} {{ $evaluation->affectation?->professeur?->prenom }}</td>
                                <td>{{ $evaluation->periode?->nom }}</td>
                                <td>{{ rtrim(rtrim(number_format((float) $evaluation->max_note, 2, '.', ''), '0'), '.') }}</td>
                                <td><span class="badge badge-{{ $evaluation->notes_count > 0 ? 'success' : 'secondary' }}">{{ $evaluation->notes_count }}</span></td>
                                <td class="text-nowrap">
                                    @if ($saisieNotesActive)
                                        <a href="{{ route('admin.evaluations.notes', $evaluation) }}" class="btn btn-sm btn-info" title="Saisir les notes"><i class="fa fa-pencil-square-o"></i></a>
                                    @else
                                        <span class="btn btn-sm btn-secondary disabled" title="Saisie des notes désactivée"><i class="fa fa-pencil-square-o"></i></span>
                                    @endif
                                    <form class="d-inline" method="POST" action="{{ route('admin.evaluations.destroy', $evaluation) }}" onsubmit="return confirm('Supprimer cette évaluation ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center text-muted">Aucune évaluation pour cette sélection.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <small class="text-muted">{{ $evaluations->total() }} évaluation(s) — page {{ $evaluations->currentPage() }} / {{ $evaluations->lastPage() }}</small>
                {{ $evaluations->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
    <script src="{{ asset('js/date-fr.js') }}"></script>
</body>
</html>
