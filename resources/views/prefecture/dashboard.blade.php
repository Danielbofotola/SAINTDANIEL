@extends('prefecture.layout')

@section('titre', 'Tableau de bord — Espace préfecture')

@section('actions')
    <span class="btn btn-light btn-round">
        <i class="far fa-calendar-alt"></i> {{ $schoolYear?->libelle ?? 'Année non définie' }}
    </span>
@endsection

@section('contenu')
    @php
        $roleLabels = [
            'admin' => 'Administrateur', 'prefetture' => 'Préfecture', 'titulaire' => 'Titulaire',
            'professeur' => 'Professeur', 'comptable' => 'Comptable', 'parent' => 'Parent',
        ];
        $personnelLignes = collect($roleLabels)->map(fn ($label, $role) => [
            'role' => $role, 'label' => $label, 'total' => (int) ($personnel[$role] ?? 0),
        ])->filter(fn ($l) => $l['total'] > 0);
        $personnelTotal = $personnelLignes->sum('total');
    @endphp

    {{-- Indicateurs --}}
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Élèves inscrits</p>
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
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="far fa-newspaper"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Classes actives</p>
                                <h4 class="card-title">{{ number_format($classesActives, 0, ',', ' ') }}</h4>
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
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Professeurs actifs</p>
                                <h4 class="card-title">{{ number_format($professeursActifs, 0, ',', ' ') }}</h4>
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
                            <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                <i class="fas fa-layer-group"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">Sections / Options</p>
                                <h4 class="card-title">{{ $sectionsCount }} / {{ $optionsCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Effectifs + personnel --}}
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
                <div class="card-header">
                    <div class="card-title">Répartition</div>
                </div>
                <div class="card-body">
                    @forelse ($effectifsParCycle as $cycle)
                        @php($part = $effectifTotal > 0 ? round($cycle->effectif * 100 / $effectifTotal, 1) : 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small class="fw-bold">{{ $cycle->nom }}</small>
                                <small class="text-muted">{{ number_format($cycle->effectif, 0, ',', ' ') }} ({{ $part }}%)</small>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" role="progressbar"
                                     style="width: {{ $part }}%" aria-valuenow="{{ $part }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucun cycle actif.</p>
                    @endforelse
                </div>
            </div>
            <div class="card card-info bg-info-gradient">
                <div class="card-body">
                    <h4 class="mb-3 fw-bold">Personnel — {{ $personnelTotal }}</h4>
                    <ul class="list-unstyled mb-0">
                        @forelse ($personnelLignes as $ligne)
                            <li class="d-flex justify-content-between pb-1 pt-1">
                                <small>{{ $ligne['label'] }}</small> <span>{{ $ligne['total'] }}</span>
                            </li>
                        @empty
                            <li><small>Aucun utilisateur.</small></li>
                        @endforelse
                    </ul>
                    <a href="{{ route('prefecture.personnel') }}" class="btn btn-light btn-sm btn-round mt-3">Voir le personnel</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Classes --}}
    <div class="row row-card-no-pd">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <h4 class="card-title">Classes actives</h4>
                        <div class="card-category">{{ $classes->count() }} classe(s)</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Classe</th>
                                    <th>Cycle</th>
                                    <th>Salle</th>
                                    <th class="text-right">Effectif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($classes as $classe)
                                    <tr>
                                        <td class="fw-bold">{{ $classe->nom }}</td>
                                        <td>{{ $classe->cycle?->nom ?? '—' }}</td>
                                        <td>{{ $classe->salle?->nom ?? '—' }}</td>
                                        <td class="text-right">{{ number_format($classe->effectif, 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Aucune classe active.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Graphique des effectifs par cycle (données réelles de l'année en cours).
        (function () {
            const canvas = document.getElementById('cyclesChart');
            if (!canvas || typeof Chart === 'undefined') return;

            const labels = @json($effectifsParCycle->pluck('nom')->all());
            const donnees = @json($effectifsParCycle->pluck('effectif')->all());

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
