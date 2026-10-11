@extends('prefecture.layout')

@section('titre', 'Périodes académiques')

@section('contenu')
    @php($enCours = $periodes->where('statut', 'actif')->count())

    <div class="row">
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="far fa-calendar-alt"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Périodes</p><h4 class="card-title">{{ $periodes->count() }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-lock-open"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Ouvertes</p><h4 class="card-title">{{ $enCours }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-layer-group"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Année en cours</p>
                        <h4 class="card-title" style="font-size:1.1rem">{{ $schoolYear?->libelle ?? '—' }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
    </div>

    <div class="row row-card-no-pd">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <h4 class="card-title">Ouverture / fermeture des périodes d'évaluation</h4>
                        <div class="card-category">Seules les périodes ouvertes acceptent de nouvelles évaluations</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Période</th>
                                    <th>État</th>
                                    <th class="text-right">Évaluations</th>
                                    <th class="text-right">Bulletins</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($periodes as $periode)
                                    <tr>
                                        <td class="fw-bold">{{ $periode->nom }}</td>
                                        <td>
                                            <span class="badge {{ $periode->statut === 'actif' ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $periode->statut === 'actif' ? 'Ouverte' : 'Fermée' }}
                                            </span>
                                        </td>
                                        <td class="text-right">{{ $periode->evaluations_count }}</td>
                                        <td class="text-right">{{ $periode->bulletins_count }}</td>
                                        <td class="text-right">
                                            <form method="POST" action="{{ route('prefecture.periodes.toggle', $periode) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-round {{ $periode->statut === 'actif' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                    <i class="fas fa-power-off"></i>
                                                    {{ $periode->statut === 'actif' ? 'Fermer' : 'Ouvrir' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">Aucune période pour l'année en cours.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
