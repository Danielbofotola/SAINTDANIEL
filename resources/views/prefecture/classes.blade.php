@extends('prefecture.layout')

@section('titre', 'Classes')

@section('actions')
    <span class="btn btn-light btn-round">
        <i class="far fa-calendar-alt"></i> {{ $schoolYear?->libelle ?? 'Année non définie' }}
    </span>
@endsection

@section('contenu')
    @php
        $totalEleves = (int) $classes->sum('effectif');
        $moyenne = $classes->count() > 0 ? round($totalEleves / $classes->count(), 1) : 0;
        $plusGrande = $classes->sortByDesc('effectif')->first();
        $vides = $classes->where('effectif', 0)->count();
    @endphp

    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-th-large"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Classes actives</p>
                                <h4 class="card-title">{{ $classes->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Élèves placés</p>
                                <h4 class="card-title">{{ number_format($totalEleves, 0, ',', ' ') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-balance-scale"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Effectif moyen</p>
                                <h4 class="card-title">{{ $moyenne }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center {{ $vides > 0 ? 'icon-danger' : 'icon-secondary' }} bubble-shadow-small">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Classes sans élève</p>
                                <h4 class="card-title">{{ $vides }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($plusGrande)
        <div class="row">
            <div class="col-md-12">
                <div class="card card-info bg-info-gradient">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="mb-1 fw-bold">Classe la plus chargée : {{ $plusGrande->nom }}</h4>
                            <small>{{ $plusGrande->cycle?->nom ?? '—' }} · {{ number_format($plusGrande->effectif, 0, ',', ' ') }} élève(s)</small>
                        </div>
                        <a href="{{ route('prefecture.effectifs') }}" class="btn btn-light btn-round btn-sm">
                            <i class="fas fa-chart-bar"></i> Voir les effectifs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row row-card-no-pd">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <h4 class="card-title">Organisation des classes</h4>
                        <div class="card-category">{{ $schoolYear?->libelle }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Classe</th>
                                    <th>Cycle</th>
                                    <th>Section</th>
                                    <th>Option</th>
                                    <th>Salle</th>
                                    <th class="text-right">Effectif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($classes as $classe)
                                    <tr>
                                        <td class="fw-bold">{{ $classe->nom }}</td>
                                        <td>{{ $classe->cycle?->nom ?? '—' }}</td>
                                        <td>{{ $classe->section?->nom ?? '—' }}</td>
                                        <td>{{ $classe->option?->nom ?? '—' }}</td>
                                        <td>{{ $classe->salle?->nom ?? '—' }}</td>
                                        <td class="text-right">
                                            @if ($classe->effectif > 0)
                                                {{ number_format($classe->effectif, 0, ',', ' ') }}
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Aucune classe active.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
