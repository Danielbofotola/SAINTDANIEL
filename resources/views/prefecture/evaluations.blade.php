@extends('prefecture.layout')

@section('titre', 'Évaluations programmées')

@section('actions')
    <form method="GET" class="form-inline">
        <select name="periode" class="form-control mr-2">
            <option value="">Toutes les périodes</option>
            @foreach ($periodes as $periode)
                <option value="{{ $periode->id }}" @selected((string) $periodeId === (string) $periode->id)>{{ $periode->nom }}</option>
            @endforeach
        </select>
        <select name="classe" class="form-control mr-2">
            <option value="">Toutes les classes</option>
            @foreach ($classes as $classe)
                <option value="{{ $classe->id }}" @selected((string) $classeId === (string) $classe->id)>{{ $classe->nom }}</option>
            @endforeach
        </select>
        <button class="btn btn-light btn-round" type="submit"><i class="fas fa-filter"></i> Filtrer</button>
    </form>
@endsection

@section('contenu')
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-clipboard-list"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Évaluations</p><h4 class="card-title">{{ $evaluations->count() }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-pen"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Notes saisies</p><h4 class="card-title">{{ number_format($notesSaisies, 0, ',', ' ') }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-tags"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Types utilisés</p><h4 class="card-title">{{ $parType->filter()->count() }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-secondary bubble-shadow-small"><i class="far fa-calendar-alt"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Périodes</p><h4 class="card-title">{{ $periodes->count() }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><div class="card-title">Répartition par type</div></div>
                <div class="card-body">
                    @forelse ($types as $cle => $libelle)
                        @php($nb = (int) ($parType[$cle] ?? 0))
                        @if ($nb > 0)
                            <div class="d-flex justify-content-between py-1">
                                <small class="fw-bold">{{ $libelle }}</small><span class="badge badge-count">{{ $nb }}</span>
                            </div>
                        @endif
                    @empty
                        <p class="text-muted mb-0">Aucune donnée.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Interrogations, devoirs et examens</div>
                        <div class="card-category">{{ $schoolYear?->libelle }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Titre</th>
                                    <th>Type</th>
                                    <th>Classe</th>
                                    <th>Cours</th>
                                    <th>Enseignant</th>
                                    <th class="text-right">Max</th>
                                    <th class="text-right">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($evaluations as $evaluation)
                                    <tr>
                                        <td>{{ $evaluation->date_evaluation?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="fw-bold">{{ $evaluation->titre }}</td>
                                        <td>{{ $types[$evaluation->type_evaluation] ?? $evaluation->type_evaluation }}</td>
                                        <td>{{ $evaluation->affectation?->classe?->nom ?? '—' }}</td>
                                        <td>{{ $evaluation->affectation?->cours?->intitule ?? '—' }}</td>
                                        <td>
                                            @php($prof = $evaluation->affectation?->professeur)
                                            {{ $prof ? trim($prof->nom.' '.$prof->prenom) : '—' }}
                                        </td>
                                        <td class="text-right">{{ (float) $evaluation->max_note }}</td>
                                        <td class="text-right">{{ $evaluation->notes_count }}</td>
                                        <td class="text-right">
                                            @if ($evaluation->validee_at)
                                                <span class="badge badge-success">
                                                    Validée {{ $evaluation->validee_at->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <form method="POST" action="{{ route('prefecture.evaluations.valider', $evaluation) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-round btn-outline-primary">
                                                        <i class="fas fa-check"></i> Valider
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted">Aucune évaluation programmée pour ce filtre.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
