@extends('prefecture.layout')

@section('titre', 'Effectifs par cycle')

@section('actions')
    <span class="btn btn-light btn-round">
        <i class="far fa-calendar-alt"></i> {{ $schoolYear?->libelle ?? 'Année non définie' }}
    </span>
@endsection

@section('contenu')
    @php
        $garcons = (int) ($parSexe['M'] ?? 0);
        $filles = (int) ($parSexe['F'] ?? 0);
    @endphp

    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Total inscrits</p>
                                <h4 class="card-title">{{ number_format($effectifTotal, 0, ',', ' ') }}</h4>
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
                            <div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-male"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Garçons</p>
                                <h4 class="card-title">{{ number_format($garcons, 0, ',', ' ') }}</h4>
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
                            <div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-female"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Filles</p>
                                <h4 class="card-title">{{ number_format($filles, 0, ',', ' ') }}</h4>
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
                            <div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-th-large"></i></div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Classes actives</p>
                                <h4 class="card-title">{{ number_format($nbClasses, 0, ',', ' ') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Effectifs par cycle</div>
                        <div class="card-category">{{ $schoolYear?->libelle }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 320px">
                        <canvas id="cyclesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><div class="card-title">Détail par cycle</div></div>
                <div class="card-body">
                    @forelse ($cycles as $cycle)
                        @php($part = $effectifTotal > 0 ? round($cycle->effectif * 100 / $effectifTotal, 1) : 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small class="fw-bold">{{ $cycle->nom }}</small>
                                <small class="text-muted">{{ number_format($cycle->effectif, 0, ',', ' ') }} ({{ $part }}%)</small>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $part }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucun cycle actif.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @foreach ($classesParCycle as $nomCycle => $classes)
        <div class="row row-card-no-pd">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-head-row">
                            <h4 class="card-title">{{ $nomCycle }}</h4>
                            <div class="card-category">
                                {{ $classes->count() }} classe(s) · {{ number_format($classes->sum('effectif'), 0, ',', ' ') }} élève(s)
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Classe</th>
                                        <th class="text-right">Effectif</th>
                                        <th class="text-right">Part du cycle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($classes as $classe)
                                        @php($totalCycle = max($classes->sum('effectif'), 1))
                                        <tr>
                                            <td class="fw-bold">{{ $classe->nom }}</td>
                                            <td class="text-right">{{ number_format($classe->effectif, 0, ',', ' ') }}</td>
                                            <td class="text-right">
                                                {{ round($classe->effectif * 100 / $totalCycle, 1) }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    <script>
        (function () {
            const canvas = document.getElementById('cyclesChart');
            if (!canvas || typeof Chart === 'undefined') return;

            const labels = @json($cycles->pluck('nom')->all());
            const donnees = @json($cycles->pluck('effectif')->all());

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Élèves inscrits',
                        data: donnees,
                        backgroundColor: '#9a6ee2',
                        borderWidth: 0,
                        barThickness: 38,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: {
                        yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }],
                        xAxes: [{ gridLines: { display: false } }],
                    },
                },
            });
        })();
    </script>
@endpush
