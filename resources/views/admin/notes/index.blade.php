<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Saisie des notes</title>
    <link rel="stylesheet" href="{{ asset('boostrap/css/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('boostrap/css/font-awesome/css/fa-solid.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <style>
        .grille-notes td, .grille-notes th { text-align: center; vertical-align: middle; }
        .grille-notes th.eleve-col { min-width: 180px; text-align: left; }
        .grille-notes input.note-input { width: 72px; text-align: center; }
        .grille-notes thead th { position: sticky; top: 0; z-index: 1; }
    </style>
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-pencil-square-o"></i> Saisie des notes</h1>
                <p>Choisissez une classe pour saisir les notes de ses élèves @if($annee) — année <strong>{{ $annee->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a href="{{ route('admin.evaluations.index') }}" class="btn btn-secondary">Évaluations</a>
                <a href="{{ route('admin.bulletins.index') }}" class="btn btn-primary"><i class="fa fa-file-text-o"></i> Bulletins</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @unless ($annee)<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Aucune année scolaire en cours.</div>@endunless
        @unless ($saisieNotesActive)<div class="alert alert-warning"><i class="fa fa-toggle-off"></i> La saisie des notes est désactivée (« Commuter les modules »).</div>@endunless

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-filter"></i> Filtrer par classe</h4>
            <form class="row" method="GET" action="{{ route('admin.notes.index') }}">
                <div class="col-md-5 form-group mb-md-0"><label class="form-label">Classe</label><select class="form-control" name="classe_id" required><option value="">Choisir une classe</option>@foreach ($classes as $c)<option value="{{ $c->id }}" @selected((string) $classeId === (string) $c->id)>{{ $c->nom }}</option>@endforeach</select></div>
                <div class="col-md-5 form-group mb-md-0"><label class="form-label">Période (optionnel)</label><select class="form-control" name="periode_id"><option value="">Toutes les périodes</option>@foreach ($periodes as $p)<option value="{{ $p->id }}" @selected((string) $periodeId === (string) $p->id)>{{ $p->nom }}</option>@endforeach</select></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-info btn-block" type="submit"><i class="fa fa-search"></i> Afficher</button></div>
            </form>
        </div>

        @if (! $classe)
            <div class="tile"><p class="text-muted mb-0">Sélectionnez une classe ci-dessus pour afficher ses élèves et saisir leurs notes.</p></div>
        @elseif ($evaluations->isEmpty())
            <div class="tile"><p class="text-muted mb-0">Aucune évaluation pour la classe {{ $classe->nom }}@if($periode) sur {{ $periode->nom }}@endif. Créez-en dans « Évaluations ».</p></div>
        @elseif ($inscriptions->isEmpty())
            <div class="tile"><p class="text-muted mb-0">Aucun élève inscrit dans la classe {{ $classe->nom }} pour l'année en cours.</p></div>
        @else
            <div class="tile">
                <h4 class="tile-title"><i class="fa fa-table"></i> {{ $classe->nom }}@if($periode) · {{ $periode->nom }}@endif — {{ $inscriptions->count() }} élève(s), {{ $evaluations->count() }} évaluation(s)</h4>
                <form method="POST" action="{{ route('admin.notes.store') }}">
                    @csrf
                    <input type="hidden" name="classe_id" value="{{ $classe->id }}">
                    <input type="hidden" name="periode_id" value="{{ $periode?->id }}">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover grille-notes">
                            <thead>
                                <tr>
                                    <th class="eleve-col">Élève</th>
                                    @foreach ($evaluations as $evaluation)
                                        <th>
                                            {{ $evaluation->affectation?->cours?->intitule ?? 'Cours' }}<br>
                                            <small class="text-muted">{{ ucfirst($evaluation->type_evaluation) }} · /{{ rtrim(rtrim(number_format((float) $evaluation->max_note, 2, '.', ''), '0'), '.') }}</small>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inscriptions as $inscription)
                                    @php($eleve = $inscription->eleve)
                                    <tr>
                                        <td class="eleve-col">{{ $eleve?->nom }} {{ $eleve?->postnom }} {{ $eleve?->prenom }}<br><small class="text-muted">{{ $eleve?->matricule }}</small></td>
                                        @foreach ($evaluations as $evaluation)
                                            <td>
                                                <input class="form-control form-control-sm note-input" type="number" step="0.01" min="0" max="{{ (float) $evaluation->max_note }}"
                                                    name="notes[{{ $eleve?->id }}][{{ $evaluation->id }}]"
                                                    value="{{ old('notes.'.$eleve?->id.'.'.$evaluation->id, data_get($notes, [$eleve?->id, $evaluation->id], '')) }}"
                                                    @disabled(! $saisieNotesActive)>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-right">
                        <button class="btn btn-primary" type="submit" @disabled(! $saisieNotesActive)><i class="fa fa-save"></i> Enregistrer les notes</button>
                    </div>
                </form>
            </div>
        @endif
    </main>
</body>
</html>
