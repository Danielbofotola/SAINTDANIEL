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
</head>
<body class="app">
    <main class="app-content" style="margin-left:0">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-pencil-square-o"></i> Saisie des notes</h1>
                <p>{{ $evaluation->titre }} — {{ $evaluation->affectation?->cours?->intitule }} · {{ $evaluation->affectation?->classe?->nom }} · {{ $evaluation->periode?->nom }} (note maximale {{ rtrim(rtrim(number_format((float) $evaluation->max_note, 2, '.', ''), '0'), '.') }})</p>
            </div>
            <div><a href="{{ route('admin.evaluations.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Retour aux évaluations</a></div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="tile">
            @if ($inscriptions->isEmpty())
                <p class="text-muted mb-0">Aucun élève inscrit dans la classe {{ $evaluation->affectation?->classe?->nom ?? '—' }} pour l'année en cours.</p>
            @else
                <form method="POST" action="{{ route('admin.evaluations.notes.store', $evaluation) }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead><tr><th>#</th><th>Matricule</th><th>Élève</th><th>Note / {{ rtrim(rtrim(number_format((float) $evaluation->max_note, 2, '.', ''), '0'), '.') }}</th><th>Observation</th></tr></thead>
                            <tbody>
                                @foreach ($inscriptions as $inscription)
                                    @php($eleve = $inscription->eleve)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $eleve?->matricule ?? '—' }}</td>
                                        <td>{{ $eleve?->nom }} {{ $eleve?->postnom }} {{ $eleve?->prenom }}</td>
                                        <td><input class="form-control" type="number" step="0.01" min="0" max="{{ (float) $evaluation->max_note }}" name="notes[{{ $eleve?->id }}]" value="{{ old('notes.'.$eleve?->id, $notes[$eleve?->id] ?? '') }}" placeholder="—"></td>
                                        <td><input class="form-control" name="observations[{{ $eleve?->id }}]" value="{{ old('observations.'.$eleve?->id, $observations[$eleve?->id] ?? '') }}" maxlength="255" placeholder="Observation (facultatif)"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-right"><button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Enregistrer les notes</button></div>
                </form>
            @endif
        </div>
    </main>
</body>
</html>
