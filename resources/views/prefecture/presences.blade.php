@extends('prefecture.layout')

@section('titre', 'Présence & ponctualité')

@section('actions')
    <form method="GET" class="form-inline">
        <input type="text" data-date-fr name="date" value="{{ $date }}" class="form-control mr-2">
        <select name="classe" class="form-control mr-2">
            <option value="">Toutes les classes</option>
            @foreach ($classes as $classe)
                <option value="{{ $classe->id }}" @selected((string) $classeId === (string) $classe->id)>{{ $classe->nom }}</option>
            @endforeach
        </select>
        <button class="btn btn-light btn-round" type="submit"><i class="fas fa-filter"></i> Afficher</button>
    </form>
@endsection

@section('contenu')
    @php
        $present = (int) ($parStatut['present'] ?? 0);
        $absent = (int) ($parStatut['absent'] ?? 0);
        $retard = (int) ($parStatut['retard'] ?? 0);
        $excuse = (int) ($parStatut['excuse'] ?? 0);
        $total = $present + $absent + $retard + $excuse;
        $assiduite = $total > 0 ? round(($present + $excuse) * 100 / $total, 1) : 0;
        $aSuivre = $presences->whereIn('statut_presence', ['absent', 'retard']);
    @endphp

    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-user-check"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Présents</p><h4 class="card-title">{{ $present }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-user-times"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Absents</p><h4 class="card-title">{{ $absent }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-user-clock"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">En retard</p><h4 class="card-title">{{ $retard }}</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body"><div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-percentage"></i></div></div>
                    <div class="col col-stats ml-3 ml-sm-0"><div class="numbers">
                        <p class="card-category">Taux d'assiduité</p><h4 class="card-title">{{ $assiduite }}%</h4>
                    </div></div>
                </div></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Élèves à suivre — {{ $date }} @if ($jour) ({{ ucfirst($jour) }}) @endif</div>
                        <div class="card-category">{{ $aSuivre->count() }} cas</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 420px">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>Élève</th><th>Matricule</th><th>Statut</th><th>Motif</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($aSuivre as $presence)
                                    <tr>
                                        <td class="fw-bold">
                                            @if ($presence->eleve)
                                                <a href="{{ route('prefecture.eleves.fiche', $presence->eleve) }}">
                                                    {{ trim($presence->eleve->nom.' '.($presence->eleve->postnom ?? '').' '.$presence->eleve->prenom) }}
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $presence->eleve?->matricule ?? '—' }}</td>
                                        <td>
                                            <span class="badge {{ $presence->statut_presence === 'absent' ? 'badge-danger' : 'badge-warning' }}">
                                                {{ \App\Models\Presence::STATUTS[$presence->statut_presence] ?? $presence->statut_presence }}
                                            </span>
                                        </td>
                                        <td>{{ $presence->motif ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Aucune absence ni retard enregistré pour cette date.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($presences->isEmpty())
                        <p class="text-muted mb-0">Aucun appel n'a encore été enregistré pour le {{ $date }} ({{ $seances }} séance(s)).</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-info bg-info-gradient">
                <div class="card-body">
                    <h4 class="mb-3 fw-bold">Enseignants attendus</h4>
                    <ul class="list-unstyled mb-0">
                        @forelse ($professeursAttendus as $professeur)
                            <li class="pb-1">
                                <i class="fas fa-chalkboard-teacher"></i>
                                {{ trim($professeur->nom.' '.($professeur->postnom ?? '').' '.$professeur->prenom) }}
                            </li>
                        @empty
                            <li><small>Aucun cours programmé ce jour.</small></li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><div class="card-title">Séances du jour</div></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-1"><small class="fw-bold">Séances appelées</small><span>{{ $seances }}</span></div>
                    <div class="d-flex justify-content-between py-1"><small class="fw-bold">Excusés</small><span>{{ $excuse }}</span></div>
                    <div class="d-flex justify-content-between py-1"><small class="fw-bold">Créneaux programmés</small><span>{{ $attendus->count() }}</span></div>
                </div>
            </div>
        </div>
    </div>
@endsection
