@extends('prefecture.layout')

@section('titre', 'Horaire des cours')

@section('actions')
    <form method="GET" class="form-inline">
        <select name="jour" class="form-control mr-2">
            <option value="">Tous les jours</option>
            @foreach ($jours as $j)
                <option value="{{ $j }}" @selected($jour === $j)>{{ ucfirst($j) }}</option>
            @endforeach
        </select>
        <select name="classe" class="form-control mr-2">
            <option value="">Toutes les classes</option>
            @foreach ($classes as $classe)
                <option value="{{ $classe->id }}" @selected((string) $classeId === (string) $classe->id)>{{ $classe->nom }}</option>
            @endforeach
        </select>
        <select name="professeur" class="form-control mr-2">
            <option value="">Tous les enseignants</option>
            @foreach ($professeurs as $professeur)
                <option value="{{ $professeur->id }}" @selected((string) $professeurId === (string) $professeur->id)>
                    {{ trim($professeur->nom.' '.$professeur->prenom) }}
                </option>
            @endforeach
        </select>
        <button class="btn btn-light btn-round" type="submit"><i class="fas fa-filter"></i> Filtrer</button>
    </form>
@endsection

@section('contenu')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info bg-info-gradient">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-0 fw-bold">{{ $total }} créneau(x) de cours</h4>
                    <small>{{ $schoolYear?->libelle }}</small>
                </div>
            </div>
        </div>
    </div>

    @forelse ($creneaux as $nomJour => $liste)
        <div class="row row-card-no-pd">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-head-row">
                            <h4 class="card-title">{{ ucfirst($nomJour) }}</h4>
                            <div class="card-category">{{ $liste->count() }} séance(s)</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Horaire</th>
                                        <th>Cours</th>
                                        <th>Classe</th>
                                        <th>Enseignant</th>
                                        <th>Salle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($liste->sortBy('heure_debut') as $creneau)
                                        <tr>
                                            <td class="fw-bold">{{ substr($creneau->heure_debut, 0, 5) }} – {{ substr($creneau->heure_fin, 0, 5) }}</td>
                                            <td>{{ $creneau->affectation?->cours?->intitule ?? '—' }}</td>
                                            <td>{{ $creneau->affectation?->classe?->nom ?? '—' }}</td>
                                            <td>
                                                @php($prof = $creneau->affectation?->professeur)
                                                {{ $prof ? trim($prof->nom.' '.$prof->prenom) : '—' }}
                                            </td>
                                            <td>{{ $creneau->salle?->nom ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="row">
            <div class="col-md-12">
                <div class="card"><div class="card-body text-center text-muted">
                    Aucun créneau ne correspond à ce filtre.
                </div></div>
            </div>
        </div>
    @endforelse
@endsection
