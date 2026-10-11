@extends('professeur.layout')

@section('titre', 'Saisie des notes')
@section('titre-page')<i class="fa fa-pencil mr-2"></i> {{ $evaluation->titre }} @endsection
@section('sous-titre')
    {{ $detail?->cours_nom }} · {{ $detail?->classe_nom }}
    · {{ $evaluation->date_evaluation?->format('d/m/Y') }}
    · note maximale {{ rtrim(rtrim(number_format((float) $evaluation->max_note, 2, ',', ' '), '0'), ',') }}
@endsection

@section('actions')
    <a class="btn btn-outline-secondary" href="{{ route('professeur.evaluations') }}"><i class="fa fa-arrow-left"></i> Évaluations</a>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="row mb-4">
        <div class="col-sm-4 mb-3"><div class="stat-card"><div class="stat-icon info"><i class="fa fa-check"></i></div><div class="stat-details"><h4>Notes saisies</h4><p>{{ $saisies }}<small>sur {{ $eleves->count() }} élève(s)</small></p></div></div></div>
        <div class="col-sm-4 mb-3"><div class="stat-card"><div class="stat-icon warning"><i class="fa fa-hourglass-half"></i></div><div class="stat-details"><h4>Restants</h4><p>{{ $restants }}</p></div></div></div>
        <div class="col-sm-4 mb-3"><div class="stat-card"><div class="stat-icon primary"><i class="fa fa-line-chart"></i></div><div class="stat-details"><h4>Moyenne</h4><p>{{ $moyenne !== null ? number_format($moyenne, 2, ',', ' ') : '—' }}</p></div></div></div>
    </div>

    <div class="tile">
        <h3 class="tile-title">Notes de la classe</h3>
        @if ($eleves->isEmpty())
            <div class="empty"><i class="fa fa-users" aria-hidden="true"></i><strong>Aucun élève inscrit</strong>Impossible de saisir des notes pour cette classe.</div>
        @else
            <form method="POST" action="{{ route('professeur.notes.store', $evaluation->id) }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th style="width:110px">Matricule</th><th>Élève</th><th style="width:130px">Note / {{ rtrim(rtrim(number_format((float) $evaluation->max_note, 2, ',', ' '), '0'), ',') }}</th><th>Observation</th></tr></thead>
                        <tbody>
                            @foreach ($eleves as $eleve)
                                @php($note = $notes[$eleve->id] ?? null)
                                <tr>
                                    <td>{{ $eleve->matricule }}</td>
                                    <td>{{ $eleve->nom }} {{ $eleve->postnom }} {{ $eleve->prenom }}</td>
                                    <td><input class="form-control form-control-sm" type="number" step="0.01" min="0" max="{{ $evaluation->max_note }}" name="notes[{{ $eleve->id }}]" value="{{ $note?->statut === 'actif' ? old('notes.'.$eleve->id, $note->note_obtenue) : old('notes.'.$eleve->id) }}"></td>
                                    <td><input class="form-control form-control-sm" type="text" maxlength="200" name="observations[{{ $eleve->id }}]" value="{{ $note?->observation }}"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Enregistrer les notes</button>
                    <span class="text-muted ml-2">Laissez vide pour effacer la note d’un élève.</span>
                </div>
            </form>
        @endif
    </div>
@endsection
