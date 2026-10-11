<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bulletins</title>
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
                <h1><i class="fa fa-file-text-o"></i> Bulletins</h1>
                <p>Consultez les résultats par période et par classe, puis générez les bulletins @if($annee) — année <strong>{{ $annee->libelle }}</strong>@endif.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('admin.evaluations.index') }}" class="btn btn-primary"><i class="fa fa-pencil"></i> Évaluations</a>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @unless ($annee)<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Aucune année scolaire en cours.</div>@endunless

        <div class="tile mb-4">
            <h4 class="tile-title"><i class="fa fa-filter"></i> Sélection</h4>
            <form class="row" method="GET" action="{{ route('admin.bulletins.index') }}">
                <div class="col-md-5 form-group mb-md-0"><label class="form-label">Période</label><select class="form-control" name="periode_id" required><option value="">Choisir une période</option>@foreach ($periodes as $p)<option value="{{ $p->id }}" @selected((string) $periodeId === (string) $p->id)>{{ $p->nom }}</option>@endforeach</select></div>
                <div class="col-md-5 form-group mb-md-0"><label class="form-label">Classe</label><select class="form-control" name="classe_id" required><option value="">Choisir une classe</option>@foreach ($classes as $c)<option value="{{ $c->id }}" @selected((string) $classeId === (string) $c->id)>{{ $c->nom }}</option>@endforeach</select></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-info btn-block" type="submit"><i class="fa fa-search"></i> Afficher</button></div>
            </form>
        </div>

        @if ($periode && $classe)
            <div class="tile">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <h4 class="tile-title mb-0"><i class="fa fa-list-ol"></i> Classement — {{ $classe->nom }} · {{ $periode->nom }}</h4>
                    @if ($rows->isNotEmpty())
                        <form method="POST" action="{{ route('admin.bulletins.generate') }}">
                            @csrf
                            <input type="hidden" name="periode_id" value="{{ $periode->id }}">
                            <input type="hidden" name="classe_id" value="{{ $classe->id }}">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-cogs"></i> Générer / actualiser les bulletins</button>
                        </form>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead><tr><th>Place</th><th>Élève</th><th>Notes</th><th>Total</th><th>Max</th><th>Pourcentage</th><th>Mention</th><th>Bulletin</th><th>Document RDC</th></tr></thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td><span class="badge badge-{{ $row['place'] <= 3 ? 'success' : 'secondary' }}">{{ $row['place'] }}</span></td>
                                    <td>{{ $row['inscription']->eleve?->nom }} {{ $row['inscription']->eleve?->postnom }} {{ $row['inscription']->eleve?->prenom }}<br><small class="text-muted">{{ $row['inscription']->eleve?->matricule }}</small></td>
                                    <td>{{ $row['nb_notes'] }}</td>
                                    <td>{{ rtrim(rtrim(number_format($row['total'], 2, '.', ''), '0'), '.') }}</td>
                                    <td>{{ rtrim(rtrim(number_format($row['max'], 2, '.', ''), '0'), '.') }}</td>
                                    <td>{{ number_format($row['pourcentage'], 2) }} %</td>
                                    <td>{{ $row['mention'] }}</td>
                                    <td>
                                        @if ($row['bulletin'])
                                            <span class="badge badge-success">Généré</span>
                                        @else
                                            <span class="badge badge-secondary">Non généré</span>
                                        @endif
                                    </td>
                                    <td><a href="{{ route('admin.bulletins.document', $row['inscription']) }}" class="btn btn-sm btn-info" target="_blank" rel="noopener" title="Bulletin annuel (norme RDC)"><i class="fa fa-file-text-o"></i> Bulletin</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted">Aucun élève inscrit dans cette classe pour l'année en cours.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif ($annee)
            <div class="tile"><p class="text-muted mb-0">Choisissez une période et une classe pour afficher le classement et générer les bulletins.</p></div>
        @endif
    </main>
</body>
</html>
